<?php
/**
 * TPMS - Native Excel/CSV Parser
 *
 * Parses .xlsx (ZIP+OpenXML), .csv, and basic .xls files
 * without any external dependencies. Requires PHP zip extension.
 *
 * Usage:
 *   $rows = ExcelParser::parse($filePath, $extension);
 *   // Returns: array of associative arrays, first row as keys
 */

class ExcelParser {

    /**
     * Parse a spreadsheet file and return rows as associative arrays.
     *
     * @param string $filePath   Absolute path to the uploaded file
     * @param string $extension  File extension: xlsx | xls | csv
     * @param int    $maxRows    Maximum rows to process (0 = unlimited)
     * @return array             [['ColA' => 'val', 'ColB' => 'val', ...], ...]
     * @throws RuntimeException  On parse failure
     */
    public static function parse(string $filePath, string $extension, int $maxRows = 2000): array {
        $extension = strtolower(trim($extension, '.'));

        switch ($extension) {
            case 'xlsx':
                return self::parseXlsx($filePath, $maxRows);
            case 'xls':
                return self::parseXls($filePath, $maxRows);
            case 'csv':
                return self::parseCsv($filePath, $maxRows);
            default:
                throw new RuntimeException("Unsupported file type: {$extension}");
        }
    }

    // =========================================================
    // XLSX Parser (ZIP + OpenXML)
    // =========================================================

    private static function parseXlsx(string $filePath, int $maxRows): array {
        if (!extension_loaded('zip')) {
            throw new RuntimeException('PHP zip extension is required to parse .xlsx files.');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException('Could not open the .xlsx file. It may be corrupt or not a valid Excel file.');
        }

        // 1. Load shared strings (string table)
        $sharedStrings = self::loadSharedStrings($zip);

        // 2. Determine the first sheet from workbook relationships
        $sheetFile = self::findFirstSheetFile($zip);

        // 3. Parse the sheet XML
        $rows = self::parseSheetXml($zip, $sheetFile, $sharedStrings, $maxRows);

        $zip->close();

        return self::rowsToAssoc($rows);
    }

    private static function loadSharedStrings(ZipArchive $zip): array {
        $strings = [];
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return $strings; // Some files inline all strings
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('s', $ns);

        // Each <si> element is one shared string; concatenate all <t> children
        $siNodes = $xpath->query('//s:si');
        if ($siNodes === false) return $strings;

        foreach ($siNodes as $si) {
            $tNodes = $xpath->query('.//s:t', $si);
            $value = '';
            foreach ($tNodes as $t) {
                $value .= $t->nodeValue;
            }
            $strings[] = $value;
        }

        return $strings;
    }

    private static function findFirstSheetFile(ZipArchive $zip): string {
        // Try workbook relationships to find sheet order
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($relsXml !== false) {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($relsXml, LIBXML_NOERROR | LIBXML_NOWARNING);
            libxml_clear_errors();
            foreach ($dom->getElementsByTagName('Relationship') as $rel) {
                $type   = $rel->getAttribute('Type');
                $target = $rel->getAttribute('Target');
                if (str_contains($type, '/worksheet')) {
                    // Target may be relative: 'worksheets/sheet1.xml'
                    if (!str_starts_with($target, '/')) {
                        $target = 'xl/' . $target;
                    }
                    return $target;
                }
            }
        }
        // Fallback
        return 'xl/worksheets/sheet1.xml';
    }

    private static function parseSheetXml(ZipArchive $zip, string $sheetFile, array $sharedStrings, int $maxRows): array {
        $xml = $zip->getFromName($sheetFile);
        if ($xml === false) {
            throw new RuntimeException("Could not read sheet data from the Excel file.");
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('s', $ns);

        $rows    = [];
        $rowNodes = $xpath->query('//s:sheetData/s:row');
        if ($rowNodes === false || $rowNodes->length === 0) return $rows;

        $count = 0;
        foreach ($rowNodes as $rowNode) {
            $row = [];
            $cellNodes = $xpath->query('.//s:c', $rowNode);
            if ($cellNodes === false) { $rows[] = []; continue; }

            // Track column positions (cells may be sparse: A1, C1, skipping B1)
            $colValues = [];

            foreach ($cellNodes as $cell) {
                $ref  = $cell->getAttribute('r'); // e.g. "A1", "B2"
                $type = $cell->getAttribute('t'); // 's' = shared string, 'n' = number, etc.
                $vNodes = $xpath->query('.//s:v', $cell);
                $value = '';
                if ($vNodes !== false && $vNodes->length > 0) {
                    $value = $vNodes->item(0)->nodeValue;
                }

                // Shared string lookup
                if ($type === 's' && isset($sharedStrings[(int)$value])) {
                    $value = $sharedStrings[(int)$value];
                } elseif ($type === 'inlineStr') {
                    $isNodes = $xpath->query('.//s:is/s:t', $cell);
                    if ($isNodes !== false && $isNodes->length > 0) {
                        $value = $isNodes->item(0)->nodeValue;
                    }
                }

                // Get the column letter from cell reference (e.g. "AB3" -> "AB")
                preg_match('/^([A-Z]+)/', $ref, $m);
                $colLetter = $m[1] ?? '';
                $colIndex  = self::colLetterToIndex($colLetter);
                $colValues[$colIndex] = trim((string)$value);
            }

            // Fill any gaps with empty strings for a dense array
            if (!empty($colValues)) {
                $maxCol = max(array_keys($colValues));
                for ($i = 0; $i <= $maxCol; $i++) {
                    $row[$i] = $colValues[$i] ?? '';
                }
            }

            $rows[] = $row;
            $count++;
            if ($maxRows > 0 && $count >= $maxRows + 1) break; // +1 for header row
        }

        return $rows;
    }

    /** Convert column letters (A, B, ..., Z, AA, AB, ...) to 0-based index */
    private static function colLetterToIndex(string $letters): int {
        $letters = strtoupper($letters);
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    // =========================================================
    // XLS Parser (Basic HTML fallback for old .xls files)
    // XLSX is strongly preferred; .xls support is best-effort.
    // =========================================================

    private static function parseXls(string $filePath, int $maxRows): array {
        // Try reading as BIFF8 by looking for HTML table content
        // Many old .xls exports are actually HTML; try that first
        $content = @file_get_contents($filePath, false, null, 0, 4096);
        if ($content === false) {
            throw new RuntimeException('Could not read the .xls file.');
        }

        // If it looks like HTML, parse it
        if (stripos($content, '<html') !== false || stripos($content, '<table') !== false) {
            return self::parseHtmlTable($filePath, $maxRows);
        }

        // BIFF binary format — we only support reading it if it's actually an xlsx renamed
        // as .xls. Try parsing as xlsx.
        try {
            return self::parseXlsx($filePath, $maxRows);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                'Legacy .xls (BIFF) format is not fully supported. Please save your file as .xlsx or .csv and try again.'
            );
        }
    }

    private static function parseHtmlTable(string $filePath, int $maxRows): array {
        $html = @file_get_contents($filePath);
        if ($html === false) throw new RuntimeException('Could not read the HTML Excel file.');

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $rows = [];
        $count = 0;
        foreach ($dom->getElementsByTagName('tr') as $tr) {
            $row = [];
            foreach ($tr->childNodes as $node) {
                if (in_array(strtolower($node->nodeName), ['td', 'th'])) {
                    $row[] = trim($node->textContent);
                }
            }
            if (!empty($row)) {
                $rows[] = $row;
                $count++;
                if ($maxRows > 0 && $count >= $maxRows + 1) break;
            }
        }
        return self::rowsToAssoc($rows);
    }

    // =========================================================
    // CSV Parser
    // =========================================================

    private static function parseCsv(string $filePath, int $maxRows): array {
        $handle = @fopen($filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException('Could not open the CSV file.');
        }

        // Detect and skip UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rows  = [];
        $count = 0;
        while (($row = fgetcsv($handle, 4096, ',')) !== false) {
            $rows[] = array_map('trim', $row);
            $count++;
            if ($maxRows > 0 && $count >= $maxRows + 1) break;
        }
        fclose($handle);

        // Try semicolon delimiter if we only got one column
        if (!empty($rows) && count($rows[0]) === 1 && str_contains($rows[0][0], ';')) {
            $handle = fopen($filePath, 'r');
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") rewind($handle);
            $rows  = [];
            $count = 0;
            while (($row = fgetcsv($handle, 4096, ';')) !== false) {
                $rows[] = array_map('trim', $row);
                $count++;
                if ($maxRows > 0 && $count >= $maxRows + 1) break;
            }
            fclose($handle);
        }

        return self::rowsToAssoc($rows);
    }

    // =========================================================
    // Shared Helpers
    // =========================================================

    /**
     * Convert a 2D array (first row = headers) to array of associative arrays.
     * Normalizes header names to lowercase with underscores.
     */
    private static function rowsToAssoc(array $rows): array {
        if (count($rows) < 1) return [];

        // Find first non-empty row as header
        $headerRow = null;
        $headerIdx = 0;
        foreach ($rows as $i => $row) {
            if (!empty(array_filter($row, fn($v) => trim($v) !== ''))) {
                $headerRow = $row;
                $headerIdx = $i;
                break;
            }
        }
        if ($headerRow === null) return [];

        // Normalize headers
        $headers = [];
        foreach ($headerRow as $h) {
            $headers[] = self::normalizeHeader((string)$h);
        }

        $result = [];
        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Skip completely empty rows
            if (empty(array_filter($row, fn($v) => trim($v) !== ''))) continue;

            $assoc = [];
            foreach ($headers as $j => $header) {
                $assoc[$header] = isset($row[$j]) ? trim((string)$row[$j]) : '';
            }
            $result[] = $assoc;
        }

        return $result;
    }

    /**
     * Normalize a header string to a canonical snake_case key.
     * This makes matching flexible regardless of capitalization or spacing.
     */
    public static function normalizeHeader(string $header): string {
        $header = trim($header);
        $header = strtolower($header);
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        $header = trim($header, '_');
        return $header;
    }

    /**
     * Map normalized header -> canonical field name
     * Allows flexible column naming in the uploaded Excel file.
     */
    public static function mapHeaders(array $row): array {
        $map = [
            // Full Name variations
            'full_name'         => 'full_name',
            'fullname'          => 'full_name',
            'name'              => 'full_name',
            'student_name'      => 'full_name',

            // Email
            'email'             => 'email',
            'email_address'     => 'email',
            'email_id'          => 'email',

            // Phone
            'phone'             => 'phone',
            'mobile'            => 'phone',
            'mobile_number'     => 'phone',
            'phone_number'      => 'phone',
            'contact'           => 'phone',
            'contact_number'    => 'phone',

            // PRN / Enrollment / Roll
            'prn'               => 'enrollment_no',
            'roll_number'       => 'enrollment_no',
            'roll_no'           => 'enrollment_no',
            'enrollment_no'     => 'enrollment_no',
            'enrollment_number' => 'enrollment_no',
            'prn_roll_number'   => 'enrollment_no',
            'prn_number'        => 'enrollment_no',

            // Registration Number
            'registration_number' => 'registration_no',
            'registration_no'     => 'registration_no',
            'reg_no'              => 'registration_no',
            'reg_number'          => 'registration_no',

            // Branch
            'branch'            => 'branch',
            'department'        => 'branch',
            'dept'              => 'branch',
            'stream'            => 'branch',

            // Semester
            'semester'          => 'semester',
            'sem'               => 'semester',
            'current_semester'  => 'semester',

            // Passing Year
            'passing_year'      => 'passing_year',
            'year_of_passing'   => 'passing_year',
            'graduation_year'   => 'passing_year',
            'pass_year'         => 'passing_year',

            // CGPA
            'cgpa'              => 'cgpa',
            'gpa'               => 'cgpa',
            'aggregate'         => 'cgpa',

            // Gender
            'gender'            => 'gender',
            'sex'               => 'gender',

            // Date of Birth
            'date_of_birth'     => 'dob',
            'dob'               => 'dob',
            'birth_date'        => 'dob',
            'birthdate'         => 'dob',

            // Skills
            'skills'            => 'skills',
            'skill_set'         => 'skills',
            'technical_skills'  => 'skills',

            // Address
            'address'           => 'address',
            'full_address'      => 'address',

            // LinkedIn
            'linkedin'          => 'linkedin',
            'linkedin_url'      => 'linkedin',
            'linkedin_profile'  => 'linkedin',

            // GitHub
            'github'            => 'github',
            'github_url'        => 'github',
            'github_profile'    => 'github',

            // Portfolio
            'portfolio'         => 'portfolio',
            'portfolio_url'     => 'portfolio',
            'website'           => 'portfolio',

            // Parent
            'parent_name'       => 'parent_name',
            'guardian_name'     => 'parent_name',
            'father_name'       => 'parent_name',

            'parent_phone'      => 'parent_phone',
            'guardian_phone'    => 'parent_phone',
            'parent_contact'    => 'parent_phone',
        ];

        $mapped = [];
        foreach ($row as $key => $value) {
            $canonical = $map[$key] ?? $key;
            $mapped[$canonical] = $value;
        }
        return $mapped;
    }

    /**
     * Generate a sample .xlsx template as a binary string.
     * Uses the OpenXML format written from scratch.
     */
    public static function generateTemplate(): string {
        $headers = [
            'Full Name', 'Email', 'Phone', 'PRN/Roll Number',
            'Registration Number', 'Branch', 'Semester', 'Passing Year',
            'CGPA', 'Gender', 'Date of Birth', 'Skills', 'Address',
            'LinkedIn', 'GitHub', 'Portfolio', 'Parent Name', 'Parent Phone'
        ];

        $sampleRows = [
            [
                'John Doe', 'john.doe@example.com', '9876543210', 'CS2024001',
                'REG2024001', 'Computer Science', '6', '2025',
                '8.50', 'Male', '2002-05-15', 'Java, Python, React', '123 Main St, Mumbai',
                'https://linkedin.com/in/johndoe', 'https://github.com/johndoe',
                'https://johndoe.dev', 'Robert Doe', '9876543200'
            ],
            [
                'Jane Smith', 'jane.smith@example.com', '9123456780', 'IT2024002',
                'REG2024002', 'Information Technology', '8', '2025',
                '9.10', 'Female', '2001-08-22', 'Python, ML, TensorFlow', '456 Park Ave, Pune',
                'https://linkedin.com/in/janesmith', 'https://github.com/janesmith',
                '', 'Mary Smith', '9123456700'
            ],
        ];

        // Build minimal XLSX from scratch
        $colWidths = array_fill(0, count($headers), 22);

        $colDefs = '';
        foreach ($colWidths as $i => $w) {
            $colDefs .= '<col min="' . ($i+1) . '" max="' . ($i+1) . '" width="' . $w . '" customWidth="1"/>';
        }

        $sheetData = '<sheetData>';

        // Header row (row 1, styled)
        $sheetData .= '<row r="1">';
        foreach ($headers as $ci => $h) {
            $col = self::indexToColLetter($ci);
            $ref = $col . '1';
            // Use shared string index (header is in sharedStrings in order)
            $sheetData .= "<c r=\"{$ref}\" t=\"inlineStr\" s=\"1\"><is><t>" . htmlspecialchars($h, ENT_XML1) . "</t></is></c>";
        }
        $sheetData .= '</row>';

        // Sample data rows
        foreach ($sampleRows as $ri => $rowData) {
            $r = $ri + 2;
            $sheetData .= "<row r=\"{$r}\">";
            foreach ($rowData as $ci => $val) {
                $col = self::indexToColLetter($ci);
                $ref = $col . $r;
                $escaped = htmlspecialchars((string)$val, ENT_XML1);
                $sheetData .= "<c r=\"{$ref}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>";
            }
            $sheetData .= '</row>';
        }
        $sheetData .= '</sheetData>';

        $numCols = count($headers);
        $lastCol = self::indexToColLetter($numCols - 1);

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView tabSelected="1" workbookViewId="0"><selection activeCell="A2" sqref="A2"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<cols>' . $colDefs . '</cols>'
            . $sheetData
            . '<printOptions gridLines="0"/>'
            . '</worksheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Students" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        // Minimal styles with header bold style (index 1 = header)
        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="10"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF4472C4"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';

        // Build the ZIP in memory
        $tmpFile = tempnam(sys_get_temp_dir(), 'tpms_template_') . '.xlsx';

        $zip = new ZipArchive();
        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create template file.');
        }

        $zip->addFromString('[Content_Types].xml',          $contentTypes);
        $zip->addFromString('_rels/.rels',                  $rootRels);
        $zip->addFromString('xl/workbook.xml',              $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels',   $workbookRels);
        $zip->addFromString('xl/worksheets/sheet1.xml',     $sheetXml);
        $zip->addFromString('xl/styles.xml',                $stylesXml);
        $zip->close();

        $content = file_get_contents($tmpFile);
        @unlink($tmpFile);

        return $content;
    }

    private static function indexToColLetter(int $index): string {
        $letters = '';
        $index++;
        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)) . $letters;
            $index   = (int)($index / 26);
        }
        return $letters;
    }
}
