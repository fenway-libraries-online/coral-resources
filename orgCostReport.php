<?php
function escape_csv($value) {
    // replace \n with \r\n
    $value = preg_replace("/(?<!\r)\n/", "\r\n", $value);
    // escape quotes
    $value = str_replace('"', '""', $value);
    return '"'.$value.'"';
}
function array_to_csv_row($array) {
    $escaped_array = array_map("escape_csv", $array);
    return implode(",",$escaped_array)."\r\n";
}
session_start();
include_once 'directory.php';
$config = new Configuration();
$orgid = $_POST['orgid'];
if ($config->settings->organizationsModule == "N") {
    $pageTitle='Home';
    include 'templates/header.php';
    echo("Org module enabled? ");
    echo($config->settings->organizationsModule);
    echo("\n");
    include 'templates/footer.php';
?>
<h2>Error</h2>
<p>
The Organizations module is not enabled.
</p>
<?php
}
else if ($orgid) {
    header("Pragma: public");
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=\"" . "foo.csv" . "\"");
    echo array_to_csv_row(array("Organization Cost Export " . format_date( date( 'Y-m-d' ))));
    echo array_to_csv_row(array(""));
    $org = new Organization(new NamedArguments(array('primaryKey' => $orgid)));;
    echo array_to_csv_row(array($org->shortName()));
    echo array_to_csv_row(array(""));
    echo array_to_csv_row(array("Resource", "Currency", "Amount", "Year", "Start date", "End date", "Fund", "Invoice", "Note"));
    $query = "
SELECT r.titleText, pmt.*
FROM   ResourcePayment pmt,
       ResourceOrganizationLink rolink,
       Resource r,
       Organization org
WHERE  pmt.resourceID = rolink.resourceID
AND    rolink.organizationID = org.organizationID
AND    rolink.resourceID = r.resourceID
AND    org.organizationID = $orgid
ORDER BY r.titleText, pmt.year, pmt.subscriptionStartDate, pmt.subscriptionEndDate
";
    $result = $org->db->processQuery($query, 'assoc');
    foreach ($result as $row) {
        $cur = $row['currencyCode'];
        $amt = $row['paymentAmount'];
        if ($cur == "USD" || $cur == "" || true) {
            $amt = $amt / 100.0;
        }
        echo array_to_csv_row(array(
            $row['titleText'],
            $cur,
            $amt,
            $row['year'],
            $row['subscriptionStartDate'],
            $row['subscriptionEndDate'],
            $row['fundName'],
            $row['invoiceNumber'],
            $row['costNote'],
        ));
    }
}
else {
    $pageTitle='Home';
    include 'templates/header.php';
?>
<h2 style="text-align: left">Cost Report - Organization Cost History</h2>
<form action="orgcost.php" method="POST" style="text-align: left">
<p>
Select Organization: 
<select name="orgid">
<?php
    $org = new Organization();
    $query = "
SELECT DISTINCT org.organizationID
FROM   ResourceOrganizationLink rolink,
   OrganizationRole orole,
   Organization org
WHERE  rolink.organizationRoleID = orole.organizationRoleID
AND    rolink.organizationID     = org.organizationID
/* AND    ( orole.shortName LIKE '%vendor%' OR orole.shortName LIKE '%seller%' OR orole.shortName IN ( 'Vendor', 'Seller', 'vendor' ) ) */
ORDER BY org.shortName
";
    $result = $org->db->processQuery($query, 'assoc');
    foreach ($result as $row) {
        $org = new Organization(new NamedArguments(array('primaryKey' => $row['organizationID'])));
?>
    <option value="<?php echo $org->organizationID() ?>"><?php echo $org->shortName() ?></option>
<?php
    }
?>
</select>
</p>
<input type="submit">
</form>
<?php
    include 'templates/footer.php';
}
?>
