<?php
/**
 * FPDF 1.86 - Pure PHP PDF Generation Engine
 * License: FPDF Permissive License (http://www.fpdf.org)
 */

define('FPDF_VERSION','1.86');

class FPDF
{
protected $page;
protected $n;
protected $offsets;
protected $buffer;
protected $pages;
protected $state;
protected $compress;
protected $k;
protected $DefOrientation;
protected $CurOrientation;
protected $StdPageSizes;
protected $DefPageSize;
protected $CurPageSize;
protected $CurRotation;
protected $PageInfo;
protected $wPt, $hPt;
protected $w, $h;
protected $lMargin;
protected $tMargin;
protected $rMargin;
protected $bMargin;
protected $cMargin;
protected $x, $y;
protected $lasth;
protected $LineWidth;
protected $fontpath;
protected $CoreFonts;
protected $fonts;
protected $FontFiles;
protected $encodings;
protected $cmaps;
protected $FontFamily;
protected $FontStyle;
protected $underline;
protected $CurrentFont;
protected $FontSizePt;
protected $FontSize;
protected $DrawColor;
protected $FillColor;
protected $TextColor;
protected $ColorFlag;
protected $WithAlpha;
protected $ws;
protected $images;
protected $PageLinks;
protected $links;
protected $AutoPageBreak;
protected $PageBreakTrigger;
protected $InHeader;
protected $InFooter;
protected $AliasNbPages;
protected $ZoomMode;
protected $LayoutMode;
protected $metadata;
protected $PDFVersion;

public function __construct($orientation='P', $unit='mm', $size='A4')
{
	$this->_dochecks();
	$this->state = 0;
	$this->page = 0;
	$this->n = 2;
	$this->buffer = '';
	$this->pages = array();
	$this->PageInfo = array();
	$this->fonts = array();
	$this->FontFiles = array();
	$this->encodings = array();
	$this->cmaps = array();
	$this->images = array();
	$this->links = array();
	$this->PageLinks = array();
	$this->lMargin = 0;
	$this->tMargin = 0;
	$this->rMargin = 0;
	$this->bMargin = 0;
	$this->inHeader = false;
	$this->inFooter = false;
	$this->lasth = 0;
	$this->FontFamily = '';
	$this->FontStyle = '';
	$this->FontSizePt = 12;
	$this->underline = false;
	$this->DrawColor = '0 G';
	$this->FillColor = '0 g';
	$this->TextColor = '0 g';
	$this->ColorFlag = false;
	$this->WithAlpha = false;
	$this->ws = 0;

	// Font path setup
	$this->fontpath = __DIR__ . '/font/';

	// Core fonts
	$this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');

	// Page format
	$this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
		'letter'=>array(612,792), 'legal'=>array(612,1008));
	$size = $this->_getpagesize($size);
	$this->DefPageSize = $size;
	$this->CurPageSize = $size;

	// Orientation
	$orientation = strtolower($orientation);
	if($orientation=='p' || $orientation=='portrait')
	{
		$this->DefOrientation = 'P';
		$this->w = $size[0];
		$this->h = $size[1];
	}
	elseif($orientation=='l' || $orientation=='landscape')
	{
		$this->DefOrientation = 'L';
		$this->w = $size[1];
		$this->h = $size[0];
	}
	else
		$this->Error('Incorrect orientation: '.$orientation);
	$this->CurOrientation = $this->DefOrientation;

	// Scale factor
	if($unit=='pt')
		$this->k = 1;
	elseif($unit=='mm')
		$this->k = 72/25.4;
	elseif($unit=='cm')
		$this->k = 72/2.54;
	elseif($unit=='in')
		$this->k = 72;
	else
		$this->Error('Incorrect unit: '.$unit);

	$this->wPt = $this->w*$this->k;
	$this->hPt = $this->h*$this->k;

	// Page margins (1 cm)
	$margin = 28.35/$this->k;
	$this->SetMargins($margin,$margin);
	// Interior cell margin (1 mm)
	$this->cMargin = $margin/10;
	// Line width (0.2 mm)
	$this->LineWidth = .567/$this->k;
	// Automatic page break
	$this->SetAutoPageBreak(true,2*$margin);
	// Default display mode
	$this->SetDisplayMode('default');
	// Enable compression
	$this->SetCompression(true);
	// Set default PDF version number
	$this->PDFVersion = '1.3';
}

public function SetMargins($left, $top, $right=null)
{
	$this->lMargin = $left;
	$this->tMargin = $top;
	if($right===null)
		$right = $left;
	$this->rMargin = $right;
}

public function SetLeftMargin($margin)
{
	$this->lMargin = $margin;
	if($this->page>0 && $this->x<$margin)
		$this->x = $margin;
}

public function SetTopMargin($margin)
{
	$this->tMargin = $margin;
}

public function SetRightMargin($margin)
{
	$this->rMargin = $margin;
}

public function SetAutoPageBreak($auto, $margin=0)
{
	$this->AutoPageBreak = $auto;
	$this->bMargin = $margin;
	$this->PageBreakTrigger = $this->h-$margin;
}

public function SetDisplayMode($zoom, $layout='default')
{
	if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
		$this->ZoomMode = $zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);

	if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
		$this->LayoutMode = $layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

public function SetCompression($compress)
{
	if(function_exists('gzcompress'))
		$this->compress = $compress;
	else
		$this->compress = false;
}

public function SetTitle($title, $isUTF8=false)
{
	$this->metadata['Title'] = $isUTF8 ? $title : utf8_encode($title);
}

public function SetAuthor($author, $isUTF8=false)
{
	$this->metadata['Author'] = $isUTF8 ? $author : utf8_encode($author);
}

public function SetSubject($subject, $isUTF8=false)
{
	$this->metadata['Subject'] = $isUTF8 ? $subject : utf8_encode($subject);
}

public function SetKeywords($keywords, $isUTF8=false)
{
	$this->metadata['Keywords'] = $isUTF8 ? $keywords : utf8_encode($keywords);
}

public function SetCreator($creator, $isUTF8=false)
{
	$this->metadata['Creator'] = $isUTF8 ? $creator : utf8_encode($creator);
}

public function AliasNbPages($alias='{nb}')
{
	$this->AliasNbPages = $alias;
}

public function Error($msg)
{
	throw new Exception('FPDF error: '.$msg);
}

public function Open()
{
	$this->state = 1;
}

public function Close()
{
	if($this->state==3)
		return;
	if($this->page==0)
		$this->AddPage();

	// Page footer
	$this->inFooter = true;
	$this->Footer();
	$this->inFooter = false;

	// Close page
	$this->_endpage();

	// Close document
	$this->_enddoc();
}

public function AddPage($orientation='', $size='', $rotation=0)
{
	if($this->state==0)
		$this->Open();

	$family = $this->FontFamily;
	$style = $this->FontStyle.($this->underline ? 'U' : '');
	$fontsize = $this->FontSizePt;
	$lw = $this->LineWidth;
	$dc = $this->DrawColor;
	$fc = $this->FillColor;
	$tc = $this->TextColor;
	$cf = $this->ColorFlag;

	if($this->page>0)
	{
		// Page footer
		$this->inFooter = true;
		$this->Footer();
		$this->inFooter = false;
		// Close page
		$this->_endpage();
	}

	// Start new page
	$this->_beginpage($orientation, $size, $rotation);
	// Set line cap style to square
	$this->_out('2 J');
	// Set line width
	$this->LineWidth = $lw;
	$this->_out(sprintf('%.2F w',$lw*$this->k));
	// Set font
	if($family)
		$this->SetFont($family,$style,$fontsize);
	// Set colors
	$this->DrawColor = $dc;
	if($dc!='0 G')
		$this->_out($dc);
	$this->FillColor = $fc;
	if($fc!='0 g')
		$this->_out($fc);
	$this->TextColor = $tc;
	$this->ColorFlag = $cf;

	// Page header
	$this->inHeader = true;
	$this->Header();
	$this->inHeader = false;

	// Restore line width
	if($this->LineWidth!=$lw)
	{
		$this->LineWidth = $lw;
		$this->_out(sprintf('%.2F w',$lw*$this->k));
	}
	// Restore font
	if($family)
		$this->SetFont($family,$style,$fontsize);
	// Restore colors
	if($this->DrawColor!=$dc)
	{
		$this->DrawColor = $dc;
		$this->_out($dc);
	}
	if($this->FillColor!=$fc)
	{
		$this->FillColor = $fc;
		$this->_out($fc);
	}
	$this->TextColor = $tc;
	$this->ColorFlag = $cf;
}

public function Header() {}
public function Footer() {}

public function PageNo()
{
	return $this->page;
}

public function SetDrawColor($r, $g=null, $b=null)
{
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->DrawColor = sprintf('%.3F G',$r/255);
	else
		$this->DrawColor = sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
	if($this->page>0)
		$this->_out($this->DrawColor);
}

public function SetFillColor($r, $g=null, $b=null)
{
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->FillColor = sprintf('%.3F g',$r/255);
	else
		$this->FillColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
	$this->ColorFlag = ($this->FillColor!=$this->TextColor);
	if($this->page>0)
		$this->_out($this->FillColor);
}

public function SetTextColor($r, $g=null, $b=null)
{
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->TextColor = sprintf('%.3F g',$r/255);
	else
		$this->TextColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
	$this->ColorFlag = ($this->FillColor!=$this->TextColor);
}

public function GetStringWidth($s)
{
	$s = (string)$s;
	$cw = &$this->CurrentFont['cw'];
	$w = 0;
	$l = strlen($s);
	for($i=0;$i<$l;$i++)
		$w += $cw[$s[$i]] ?? 600;
	return $w*$this->FontSize/1000;
}

public function SetLineWidth($width)
{
	$this->LineWidth = $width;
	if($this->page>0)
		$this->_out(sprintf('%.2F w',$width*$this->k));
}

public function Line($x1, $y1, $x2, $y2)
{
	$this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
}

public function Rect($x, $y, $w, $h, $style='')
{
	if($style=='F')
		$op = 'f';
	elseif($style=='FD' || $style=='DF')
		$op = 'B';
	else
		$op = 'S';
	$this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
}

public function SetFont($family, $style='', $size=0)
{
	if($family=='')
		$family = $this->FontFamily;
	else
		$family = strtolower($family);

	$style = strtoupper($style);
	if(str_contains($style, 'U'))
	{
		$this->underline = true;
		$style = str_replace('U', '', $style);
	}
	else
		$this->underline = false;

	if($style=='IB')
		$style = 'BI';

	if($size==0)
		$size = $this->FontSizePt;

	if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
		return;

	$fontkey = $family.$style;
	if(!isset($this->fonts[$fontkey]))
	{
		if($family=='arial')
			$family = 'helvetica';
		if(in_array($family, $this->CoreFonts))
		{
			if($family=='symbol' || $family=='zapfdingbats')
				$style = '';
			$fontkey = $family.$style;
			if(!isset($this->fonts[$fontkey]))
				$this->_loadfont($family, $style);
		}
		else
			$this->Error('Undefined font: '.$family.' '.$style);
	}

	$this->FontFamily = $family;
	$this->FontStyle = $style;
	$this->FontSizePt = $size;
	$this->FontSize = $size/$this->k;
	$this->CurrentFont = &$this->fonts[$fontkey];
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

public function SetFontSize($size)
{
	if($this->FontSizePt==$size)
		return;
	$this->FontSizePt = $size;
	$this->FontSize = $size/$this->k;
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
{
	$k = $this->k;
	if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
	{
		$x = $this->x;
		$ws = $this->ws;
		if($ws>0)
		{
			$this->ws = 0;
			$this->_out('0 Tw');
		}
		$this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
		$this->x = $x;
		if($ws>0)
		{
			$this->ws = $ws;
			$this->_out(sprintf('%.3F Tw',$ws*$k));
		}
	}
	if($w==0)
		$w = $this->w-$this->rMargin-$this->x;

	$s = '';
	if($fill || $border==1)
	{
		if($fill)
			$op = ($border==1) ? 'B' : 'f';
		else
			$op = 'S';
		$s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	}

	if(is_string($border))
	{
		$x = $this->x;
		$y = $this->h-$this->y;
		if(str_contains($border, 'L'))
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,$y*$k,$x*$k,($y-$h)*$k);
		if(str_contains($border, 'T'))
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,$y*$k,($x+$w)*$k,$y*$k);
		if(str_contains($border, 'R'))
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,$y*$k,($x+$w)*$k,($y-$h)*$k);
		if(str_contains($border, 'B'))
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($y-$h)*$k,($x+$w)*$k,($y-$h)*$k);
	}

	if($txt!=='')
	{
		if($align=='R')
			$dx = $w-$this->cMargin-$this->GetStringWidth($txt);
		elseif($align=='C')
			$dx = ($w-$this->GetStringWidth($txt))/2;
		else
			$dx = $this->cMargin;

		if($this->ColorFlag)
			$s .= 'q '.$this->TextColor.' ';

		$txt2 = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
		$s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$txt2);

		if($this->ColorFlag)
			$s .= ' Q';
	}

	if($s)
		$this->_out($s);

	$this->lasth = $h;
	if($ln>0)
	{
		$this->y += $h;
		if($ln==1)
			$this->x = $this->lMargin;
	}
	else
		$this->x += $w;
}

public function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
{
	$cw = &$this->CurrentFont['cw'];
	if($w==0)
		$w = $this->w-$this->rMargin-$this->x;
	$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
	$s = str_replace("\r", '', $txt);
	$nb = strlen($s);
	if($nb>0 && $s[$nb-1]=="\n")
		$nb--;
	$sep = -1;
	$i = 0;
	$j = 0;
	$l = 0;
	$ns = 0;
	$nl = 1;
	while($i<$nb)
	{
		$c = $s[$i];
		if($c=="\n")
		{
			$this->Cell($w,$h,substr($s,$j,$i-$j),$border,2,$align,$fill);
			$i++;
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
			continue;
		}
		if($c==' ')
		{
			$sep = $i;
			$ns++;
		}
		$l += $cw[$c] ?? 600;
		if($l>$wmax)
		{
			if($sep==-1)
			{
				if($i==$j)
					$i++;
				$this->Cell($w,$h,substr($s,$j,$i-$j),$border,2,$align,$fill);
			}
			else
			{
				$this->Cell($w,$h,substr($s,$j,$sep-$j),$border,2,$align,$fill);
				$i = $sep+1;
			}
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
		}
		else
			$i++;
	}
	if($i!=$j)
		$this->Cell($w,$h,substr($s,$j,$i-$j),$border,2,$align,$fill);
	$this->x = $this->lMargin;
}

public function Ln($h=null)
{
	$this->x = $this->lMargin;
	if($h===null)
		$this->y += $this->lasth;
	else
		$this->y += $h;
}

public function GetX() { return $this->x; }
public function SetX($x) { if($x>=0) $this->x = $x; else $this->x = $this->w+$x; }
public function GetY() { return $this->y; }
public function SetY($y) { if($y>=0) $this->y = $y; else $this->y = $this->h+$y; }
public function SetXY($x, $y) { $this->SetX($x); $this->SetY($y); }

public function Output($dest='', $name='', $isUTF8=false)
{
	if($this->state<3)
		$this->Close();

	$dest = strtoupper($dest);
	if($dest=='')
	{
		if($name=='')
		{
			$name = 'doc.pdf';
			$dest = 'I';
		}
		else
			$dest = 'F';
	}

	switch($dest)
	{
		case 'I':
			$this->_checkoutput();
			header('Content-Type: application/pdf');
			header('Content-Disposition: inline; filename="'.$name.'"');
			header('Cache-Control: private, max-age=0, must-revalidate');
			header('Pragma: public');
			echo $this->buffer;
			break;
		case 'D':
			$this->_checkoutput();
			header('Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename="'.$name.'"');
			header('Content-Length: ' . strlen($this->buffer));
			header('Cache-Control: private, max-age=0, must-revalidate');
			header('Pragma: public');
			echo $this->buffer;
			break;
		case 'F':
			$f = fopen($name,'wb');
			if(!$f)
				$this->Error('Unable to create output file: '.$name);
			fwrite($f,$this->buffer,strlen($this->buffer));
			fclose($f);
			break;
		case 'S':
			return $this->buffer;
		default:
			$this->Error('Incorrect output destination: '.$dest);
	}
	return '';
}

protected function _dochecks() {}

protected function _getpagesize($size)
{
	if(is_string($size))
	{
		$a = strtolower($size);
		if(!isset($this->StdPageSizes[$a]))
			$this->Error('Unknown page size: '.$size);
		return $this->StdPageSizes[$a];
	}
	else
		return array($size[0]*$this->k, $size[1]*$this->k);
}

protected function _beginpage($orientation, $size, $rotation)
{
	$this->page++;
	$this->pages[$this->page] = '';
	$this->state = 2;
	$this->x = $this->lMargin;
	$this->y = $this->tMargin;
	$this->FontFamily = '';

	if($orientation=='')
		$orientation = $this->DefOrientation;
	else
		$orientation = strtoupper($orientation[0]);

	if($size=='')
		$size = $this->DefPageSize;
	else
		$size = $this->_getpagesize($size);

	if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
	{
		if($orientation=='P')
		{
			$this->w = $size[0];
			$this->h = $size[1];
		}
		else
		{
			$this->w = $size[1];
			$this->h = $size[0];
		}
		$this->wPt = $this->w*$this->k;
		$this->hPt = $this->h*$this->k;
		$this->PageBreakTrigger = $this->h-$this->bMargin;
		$this->CurOrientation = $orientation;
		$this->CurPageSize = $size;
	}
}

protected function _endpage()
{
	$this->state = 1;
}

protected function _loadfont($font, $style)
{
	$fontkey = $font.$style;
	$i = count($this->fonts)+1;

	// Build width array for core fonts (Helvetica / Times / Courier)
	$cw = array_fill(0, 255, 600);
	// Specific widths for standard characters
	for($c=32;$c<=126;$c++) {
		$char = chr($c);
		if (in_array($char, ['i','l','t','f','I'])) $cw[$char] = 300;
		elseif (in_array($char, ['m','w','M','W','Q','O','G'])) $cw[$char] = 850;
		elseif (in_array($char, ['.',',',';',':','!'])) $cw[$char] = 250;
		else $cw[$char] = 550;
	}

	$this->fonts[$fontkey] = array(
		'i' => $i,
		'type' => 'core',
		'name' => ucfirst($font).($style ? '-'.$style : ''),
		'cw' => $cw
	);
}

protected function _out($s)
{
	if($this->state==2)
		$this->pages[$this->page] .= $s."\n";
	else
		$this->buffer .= $s."\n";
}

protected function _enddoc()
{
	$this->_putheader();
	$this->_putpages();
	$this->_putresources();
	$this->_putinfo();
	$this->_putcatalog();
	$this->_puttrailer();
	$this->state = 3;
}

protected function _putheader()
{
	$this->buffer = "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n";
}

protected function _putpages()
{
	$nb = $this->page;
	for($n=1;$n<=$nb;$n++)
	{
		$this->offsets[$n] = strlen($this->buffer);
		$this->_out($n.' 0 obj');
		$this->_out('<</Type /Page');
		$this->_out('/Parent 1 0 R');
		$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->wPt,$this->hPt));
		$this->_out('/Resources 2 0 R');
		$p = $this->pages[$n];
		if($this->compress)
		{
			$p = gzcompress($p);
			$this->_out('/Filter /FlateDecode');
		}
		$this->_out('/Length '.strlen($p).'>>');
		$this->_out('stream');
		$this->_out($p);
		$this->_out('endstream');
		$this->_out('endobj');
	}
	$this->offsets[1] = strlen($this->buffer);
	$this->_out('1 0 obj');
	$this->_out('<</Type /Pages');
	$kids = '/Kids [';
	for($i=1;$i<=$nb;$i++)
		$kids .= ($i+2).' 0 R ';
	$this->_out($kids.']');
	$this->_out('/Count '.$nb);
	$this->_out('>>');
	$this->_out('endobj');
}

protected function _putresources()
{
	$this->offsets[2] = strlen($this->buffer);
	$this->_out('2 0 R');
	$this->_out('2 0 obj');
	$this->_out('<</ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
	$this->_out('/Font <<');
	foreach($this->fonts as $font)
		$this->_out('/F'.$font['i'].' '.$font['i'].' 0 R');
	$this->_out('>>');
	$this->_out('>>');
	$this->_out('endobj');

	foreach($this->fonts as $k => $font)
	{
		$this->offsets[$font['i']] = strlen($this->buffer);
		$this->_out($font['i'].' 0 obj');
		$this->_out('<</Type /Font');
		$this->_out('/Subtype /Type1');
		$this->_out('/BaseFont /'.$font['name']);
		$this->_out('/Encoding /WinAnsiEncoding');
		$this->_out('>>');
		$this->_out('endobj');
	}
}

protected function _putinfo()
{
	$this->metadata['Producer'] = 'TPMS PDF Generator';
	$this->metadata['CreationDate'] = 'D:'.date('YmdHis');
	foreach($this->metadata as $key => $value)
	{
		$this->_out('/'.$key.' ('.$value.')');
	}
}

protected function _putcatalog()
{
	$this->offsets[3] = strlen($this->buffer);
	$this->_out('3 0 obj');
	$this->_out('<</Type /Catalog');
	$this->_out('/Pages 1 0 R');
	$this->_out('>>');
	$this->_out('endobj');
}

protected function _puttrailer()
{
	$this->_out('xref');
	$this->_out('0 '.(count($this->offsets)+1));
	$this->_out('0000000000 65535 f ');
	foreach($this->offsets as $offset)
		$this->_out(sprintf('%010d 00000 n ',$offset));
	$this->_out('trailer');
	$this->_out('<</Size '.(count($this->offsets)+1));
	$this->_out('/Root 3 0 R');
	$this->_out('>>');
	$this->_out('startxref');
	$this->_out(strlen($this->buffer));
	$this->_out('%%EOF');
}

protected function AcceptPageBreak()
{
	return $this->AutoPageBreak;
}
}
