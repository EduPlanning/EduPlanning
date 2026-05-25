<?php
include_once '../../config/headers.php';
include_once '../../middleware/auth.php';
requireAuth();

include_once '../../config/Database.php';
include_once '../../models/Creneau.php';

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['message' => 'ZipArchive est requis pour l’export XLSX.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function xmlEscape($value)
{
    return htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function columnName($index)
{
    $name = '';
    while ($index > 0) {
        $index--;
        $name = chr(65 + ($index % 26)) . $name;
        $index = (int) floor($index / 26);
    }
    return $name;
}

$database = new Database();
$db = $database->connect();
$creneau = new Creneau($db);

$filters = [];
foreach (['groupe_id', 'enseignant_id', 'salle_id'] as $key) {
    if (isset($_GET[$key])) {
        $filters[$key] = (int) $_GET[$key];
    }
}
if (isset($_GET['date_debut'])) {
    $filters['date_debut'] = $_GET['date_debut'];
}
if (isset($_GET['date_fin'])) {
    $filters['date_fin'] = $_GET['date_fin'];
}

$result = $creneau->getAll($filters);
$rows = [[
    'Date',
    'Heure début',
    'Heure fin',
    'Matière',
    'Code matière',
    'Enseignant',
    'Salle',
    'Groupe',
    'Type'
]];

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = [
        $row['date_cours'],
        substr((string) $row['heure_debut'], 0, 5),
        substr((string) $row['heure_fin'], 0, 5),
        $row['matiere_nom'] ?? '',
        $row['matiere_code'] ?? '',
        $row['enseignant_nom'] ?? '',
        $row['salle_nom'] ?? '',
        $row['groupe_nom'] ?? '',
        strtoupper((string) ($row['type'] ?? ''))
    ];
}

$sheetRows = '';
foreach ($rows as $rowIndex => $row) {
    $excelRow = $rowIndex + 1;
    $sheetRows .= '<row r="' . $excelRow . '">';
    foreach ($row as $colIndex => $cellValue) {
        $ref = columnName($colIndex + 1) . $excelRow;
        $sheetRows .= '<c r="' . $ref . '" t="inlineStr"><is><t>' . xmlEscape($cellValue) . '</t></is></c>';
    }
    $sheetRows .= '</row>';
}

$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
    . '<sheetData>' . $sheetRows . '</sheetData>'
    . '</worksheet>';

$tempFile = tempnam(sys_get_temp_dir(), 'eduplanning_xlsx_');
$zip = new ZipArchive();
$zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    . '<Default Extension="xml" ContentType="application/xml"/>'
    . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
    . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
    . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
    . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
    . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
    . '</Types>');
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
    . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
    . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
    . '</Relationships>');
$zip->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
    . '<dc:title>Export planning EduPlanning</dc:title>'
    . '<dc:creator>EduPlanning</dc:creator>'
    . '<cp:lastModifiedBy>EduPlanning</cp:lastModifiedBy>'
    . '<dcterms:created xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:created>'
    . '</cp:coreProperties>');
$zip->addFromString('docProps/app.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
    . '<Application>EduPlanning</Application>'
    . '</Properties>');
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
    . '<sheets><sheet name="Planning" sheetId="1" r:id="rId1"/></sheets>'
    . '</workbook>');
$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
    . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
    . '</Relationships>');
$zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
    . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
    . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
    . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
    . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
    . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
    . '</styleSheet>');
$zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
$zip->close();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="planning.xlsx"');
header('Content-Length: ' . filesize($tempFile));
readfile($tempFile);
unlink($tempFile);
?>
