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
$orgid = $_POST['org'];
if (!$orgid) {
    $orgid = $_GET['org'];
}
$config = new Configuration();
$error = null;
if ($config->settings->organizationsModule == "N") {
    $pageTitle = "Error";
    $fmt = "html";
    $error = "The Organizations module is not enabled";
}
else if ($orgid) {
    $pageTitle = "Organization Cost Report";
    $fmt = $_POST['fmt'];
    if (!$fmt) {
        $fmt = $_GET['fmt'];
    }
}
else {
    $pageTitle = "Organization Cost Report";
    $fmt = "html";
}
if ($fmt == "html") {
    include 'templates/header.php';
    if ($error) {
        echo "<p>" . $error . "</p>";
    }
    else {
?>
<h2 style="text-align: left">Organization Cost Report</h2>
<p style="text-align: left; margin-top: 0.5em; margin-bottom: 0.5em;">
Use this form to generate a report of all payments for resources associated with an organization.
</p>
<form action="orgCostReport.php" method="POST" style="text-align: left">
<p style="text-align: left; margin-top: 0.5em; margin-bottom: 0.5em;">
Organization: 
<select name="org">
<?php
    $org = new Organization();
    $query = "
SELECT DISTINCT org.organizationID
FROM   ResourceOrganizationLink rolink,
       OrganizationRole         orole,
       Organization             org
WHERE  rolink.organizationID     = org.organizationID
AND    rolink.organizationRoleID = orole.organizationRoleID
/*
AND    ( orole.shortName LIKE '%vendor%' OR orole.shortName LIKE '%seller%' OR orole.shortName IN ( 'Vendor', 'Seller', 'vendor' ) ) */
ORDER BY org.shortName
";
    $result = $org->db->processQuery($query, 'assoc');
    foreach ($result as $row) {
        $org = new Organization(new NamedArguments(array('primaryKey' => $row['organizationID'])));
        if ($orgid && $row['organizationID'] == $orgid) {
            printf("<option value='%s' selected>%s</option>\n", $org->organizationID(), $org->shortName());
        }
        else {
            printf("<option value='%s'>%s</option>\n", $org->organizationID(), $org->shortName());
        }
    }
?>
</select>
<input type="hidden" name="fmt" value="html">
</p>
<p style="text-align: left; margin-top: 0.5em; margin-bottom: 0.5em;">
<input type="submit" value="Generate Report"></input>
</p>
</form>
<?php
    }
}
if ($orgid) {
    // Generate the report
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
    $org = new Organization(new NamedArguments(array('primaryKey' => $orgid)));;
    $result = $org->db->processQuery($query, 'assoc');
    $title = "Organization Cost Report";
    $date = date('Y-m-d');
    $orgName = $org->shortName();
    if ($fmt == "csv") {
        header("Pragma: public");
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=\"" . "foo.csv" . "\"");
        echo array_to_csv_row(array($title));
        echo array_to_csv_row(array($date));
        echo array_to_csv_row(array(""));
        echo array_to_csv_row(array("Organization", "Resource", "Amount", "Currency", "Year", "Start date", "End date", "Fund", "Invoice", "Note"));
        foreach ($result as $row) {
            $cur = $row['currencyCode'];
            $amt = $row['paymentAmount'];
            if ($cur == "USD" || $cur == "" || true) {
                $amt = sprintf("%.2f", $amt / 100.0);
            }
            echo array_to_csv_row(array(
                $orgName,
                $row['titleText'],
                $amt,
                $cur,
                $row['year'],
                $row['subscriptionStartDate'],
                $row['subscriptionEndDate'],
                $row['fundName'],
                $row['invoiceNumber'],
                $row['costNote'],
            ));
        }
    }
    else if (!$error) {
?>
<h2 style="text-align: left"><?php echo $title; ?></h2>
<p style="text-align: left; margin-top: 0.5em; margin-bottom: 0.5em"><?php echo "Generated " . $date; ?></p>
<h3 style="text-align: left; margin-top: 0.5em; margin-bottom: 0.5em"><?php echo $orgName; ?></h3>
<p style="text-align: left; margin-bottom: 0.5em"><a href="orgCostReport.php?org=<?php echo $orgid ?>&fmt=csv"><img src="images/xls.gif"> Download as CSV</a></p>
<table class="dataTable">
    <thead>
    <tr>
        <th>Resource</th>
        <th style="text-align: right">Amount</th>
        <th>Currency</th>
        <th>Year</th>
        <th>Start date</th>
        <th>End date</th>
        <th>Fund</th>
        <th>Invoice</th>
        <th>Note</th>
    </tr>
    </thead>
    <tbody>
<?php
        foreach ($result as $row) {
            $cur = $row['currencyCode'];
            $amt = $row['paymentAmount'];
            if ($cur == "USD" || $cur == "" || true) {
                $amt = sprintf("\$%.2f", $amt / 100.0);
            }
            $T = "<td>%s</td>\n";
            $U = "<td><a href='resource.php?resourceID=%d'>%s</a></td>\n";
            $R = "<td style='text-align: right'>%s</td>\n";
            echo "<tr>\n";
                printf($U, $row['resourceID'], $row['titleText']);
                printf($R, $amt);
                printf($T, $cur);
                printf($T, $row['year'                  ]);
                printf($T, $row['subscriptionStartDate' ]);
                printf($T, $row['subscriptionEndDate'   ]);
                printf($T, $row['fundName'              ]);
                printf($T, $row['invoiceNumber'         ]);
                printf($T, $row['costNote'              ]);
            echo "</tr>\n";
        }
?>
    </tbody>
</table>
<?php
    }
}
if ($fmt == "html") {
    include 'templates/footer.php';
}
// vim: set et ai si cindent noincsearch nohls ts=4 sw=4:
?>
