<?php
/*
 * Inventory Transactions report
 * This is an inventory transactions list.
 *
 * Copyright (C) 2016-2017 Terry Hill <teryhill@librehealth.io>
 * Copyright (C) 2010-2015 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * LICENSE: This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0
 * See the Mozilla Public License for more details.
 * If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @package LibreHealth EHR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @link http://librehealth.io
 */

require_once "reports_controllers/InventoryTransactionsController.php";

if ($form_action == 'export') {
  csvexport('inventory_transactions'); // CSV headers. (TRK)
  // CSV headers:
  echo '"' . xl('Date'       ) . '",';
  echo '"' . xl('Transaction') . '",';
  echo '"' . xl('Product'    ) . '",';
  echo '"' . xl('Lot'        ) . '",';
  echo '"' . xl('Warehouse'  ) . '",';
  echo '"' . xl('Who'        ) . '",';
  echo '"' . xl('Qty'        ) . '",';
  echo '"' . xl('Amount'     ) . '",';
  echo '"' . xl('Billed'     ) . '",';
  echo '"' . xl('Notes'      ) . '"' . "\n";
} // end export
else {
?>

<html>
<head>
<?php html_header_show(); ?>
<title><?php echo htmlspecialchars(xl('Inventory Transactions'), ENT_NOQUOTES) ?></title>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<style type="text/css">
 /* specifically include & exclude from printing */
 @media print {
  #report_parameters {visibility: hidden; display: none;}
  #report_parameters_daterange {visibility: visible; display: inline;}
  #report_results {margin-top: 30px;}
 }
 /* specifically exclude some from the screen */
 @media screen {
  #report_parameters_daterange {visibility: hidden; display: none;}
 }
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.9.1.min.js"></script>

<script language='JavaScript'>

 $(document).ready(function() {
  var win = top.printLogSetup ? top : opener.top;
  win.printLogSetup(document.getElementById('printbutton'));
 });

 function mysubmit(action) {
  var f = document.forms[0];
  f.form_action.value = action;
  top.restoreSession();
  f.submit();
 }

</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class='body_top'>
<center>

<h2><?php echo htmlspecialchars(xl('Inventory Transactions'), ENT_NOQUOTES) ?></h2>

<form method='post' action='inventory_transactions.php'>

<div id="report_parameters">
<!-- form_action is set to "submit" or "export" at form submit time -->
<input type='hidden' name='form_action' value='' />
<table>
 <tr>
  <td width='50%'>
   <table class='text'>
    <tr>
     <td class='label'>
      <?php echo htmlspecialchars(xl('Type'), ENT_NOQUOTES); ?>:
     </td>
     <td nowrap>
      <select name='form_trans_type' onchange='trans_type_changed()'>
<?php
foreach (array(
  '0' => xl('All'),
  '2' => xl('Purchase/Return'),
  '1' => xl('Sale'),
  '6' => xl('Distribution'),
  '4' => xl('Transfer'),
  '5' => xl('Adjustment'),
) as $key => $value)
{
  echo "       <option value='$key'";
  if ($key == $form_trans_type) echo " selected";
  echo ">" . htmlspecialchars($value, ENT_NOQUOTES) . "</option>\n";
}
?>
      </select>
     </td>
     <?php // Show From and To dates fields. (TRK)
      showFromAndToDates(); ?>
    </tr>
   </table>
  </td>
  <td align='left' valign='middle'>
   <table style='border-left:1px solid; width:100%; height:100%'>
    <tr>
     <td valign='middle'>
      <a href='#' class='css_button cp-submit' onclick='mysubmit("submit")' style='margin-left:1em'>
       <span><?php echo htmlspecialchars(xl('Submit'), ENT_NOQUOTES); ?></span>
      </a>
<?php if ($form_action) { ?>
      <a href='#' class='css_button cp-output' id='printbutton' style='margin-left:1em'>
       <span><?php echo htmlspecialchars(xl('Print'), ENT_NOQUOTES); ?></span>
      </a>
      <a href='#' class='css_button cp-output' onclick='mysubmit("export")' style='margin-left:1em'>
       <span><?php echo htmlspecialchars(xl('CSV Export'), ENT_NOQUOTES); ?></span>
      </a>
<?php } ?>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</div>

<?php if ($form_action) { // if submit (already not export here) ?>

<div id="report_results">
<table border='0' cellpadding='1' cellspacing='2' width='98%'>
 <tr bgcolor="#dddddd">
  <td class="dehead">
   <?php echo htmlspecialchars(xl('Date'), ENT_NOQUOTES); ?>
  </td>
  <td class="dehead">
   <?php echo htmlspecialchars(xl('Transaction'), ENT_NOQUOTES); ?>
  </td>
  <td class="dehead">
   <?php echo htmlspecialchars(xl('Product'), ENT_NOQUOTES); ?>
  </td>
  <td class="dehead">
   <?php echo htmlspecialchars(xl('Lot'), ENT_NOQUOTES); ?>
  </td>
  <td class="dehead">
   <?php echo htmlspecialchars(xl('Warehouse'), ENT_NOQUOTES); ?>
  </td>
  <td class="dehead">
   <?php echo htmlspecialchars(xl('Who'), ENT_NOQUOTES); ?>
  </td>
  <td class="dehead" align="right">
   <?php echo htmlspecialchars(xl('Qty'), ENT_NOQUOTES); ?>
  </td>
  <td class="dehead" align="right">
   <?php echo htmlspecialchars(xl('Amount'), ENT_NOQUOTES); ?>
  </td>
  <td class="dehead" align="Center">
   <?php echo htmlspecialchars(xl('Billed'), ENT_NOQUOTES); ?>
  </td>
  <td class="dehead">
   <?php echo htmlspecialchars(xl('Notes'), ENT_NOQUOTES); ?>
  </td>
 </tr>
<?php
} // end if submit
} // end not export

if ($form_action) { // if submit or export
  prepareAndShowResults(); // Prepare and show the result. (TRK)
} // end if submit or export

if ($form_action != 'export') {
  if ($form_action) {
?>
</table>
</div>
<?php
  } // end if ($form_action)
?>

</form>
</center>
</body>

<link rel="stylesheet" href="../../library/css/jquery.datetimepicker.css">
<script type="text/javascript" src="../../library/js/jquery.datetimepicker.full.min.js"></script>
<script>
    $(function() {
        $("#form_from_date").datetimepicker({
            timepicker: false,
            format: "<?= $DateFormat; ?>"
        });
        $("#form_to_date").datetimepicker({
            timepicker: false,
            format: "<?= $DateFormat; ?>"
        });
        $.datetimepicker.setLocale('<?= $DateLocale; ?>');
    });
</script>

</html>
<?php
} // End not export
?>
