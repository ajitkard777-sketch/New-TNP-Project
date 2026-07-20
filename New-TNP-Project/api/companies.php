<?php
/**
 * TPMS — api/companies.php
 * Admin CRUD for companies (recruiter partners).
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

setApiHeaders();
tpms_session_start();

$action = get_param('action') ?: post('action') ?: 'list';

switch ($action) {
    case 'list':   handleList();   break;
    case 'create': handleCreate(); break;
    case 'delete': handleDelete(); break;
    default: respond(['success' => false, 'message' => 'Unknown action'], 400);
}

function handleList(): void
{
    requireRole('admin');
    $pdo    = getPDO();
    $search = get_param('search');
    $ind    = get_param('industry');

    $where  = ['1=1'];
    $params = [];
    if ($search) { $where[] = 'name LIKE ?';     $params[] = "%$search%"; }
    if ($ind)    { $where[] = 'industry LIKE ?'; $params[] = "%$ind%"; }

    $sql  = "SELECT comp_uid AS id, name, website, industry, contact,
                    DATE_FORMAT(registered_date,'%Y-%m-%d') AS registeredDate,
                    job_count AS jobCount, logo_url AS companyLogo
             FROM companies WHERE " . implode(' AND ', $where) . " ORDER BY name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    respond(['success' => true, 'companies' => $stmt->fetchAll()]);
}

function handleCreate(): void
{
    requireRole('admin');
    validateCSRF();

    $name    = post('name');
    $web     = post('website');
    $ind     = post('industry');
    $contact = post('contact');

    if (empty($name)) respond(['success' => false, 'message' => 'Company name required.'], 422);

    $pdo   = getPDO();
    $uid   = 'COMP' . substr((string)(microtime(true) * 10000), -6);

    $pdo->prepare(
        "INSERT INTO companies (comp_uid,name,website,industry,contact,registered_date,job_count)
         VALUES (?,?,?,?,?,CURDATE(),0)"
    )->execute([$uid, $name, $web, $ind, $contact]);

    $pdo->prepare("INSERT INTO activities (type,text,icon) VALUES (?,?,?)")
        ->execute(['registration', "New recruiter registered: $name", 'user-plus']);

    $company = [
        'id'           => $uid,
        'name'         => $name,
        'website'      => $web,
        'industry'     => $ind,
        'contact'      => $contact,
        'registeredDate'=> date('Y-m-d'),
        'jobCount'     => 0,
        'companyLogo'  => '',
    ];
    respond(['success' => true, 'message' => "Partner \"$name\" registered successfully!", 'company' => $company]);
}

function handleDelete(): void
{
    requireRole('admin');
    validateCSRF();

    $uid = post('id') ?: get_param('id');
    $pdo = getPDO();

    $stmt = $pdo->prepare("DELETE FROM companies WHERE comp_uid=?");
    $stmt->execute([$uid]);

    if ($stmt->rowCount() === 0)
        respond(['success' => false, 'message' => 'Company not found.'], 404);

    respond(['success' => true, 'message' => "Company $uid removed."]);
}
