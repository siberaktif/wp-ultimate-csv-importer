<?php
/*********************************************************************************
 * WP Ultimate CSV Importer is a Tool for importing CSV for the Wordpress
 * plugin developed by Smackcoder. Copyright (C) 2014 Smackcoders.
 *
 * WP Ultimate CSV Importer is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License version 3
 * as published by the Free Software Foundation with the addition of the
 * following permission added to Section 15 as permitted in Section 7(a): FOR
 * ANY PART OF THE COVERED WORK IN WHICH THE COPYRIGHT IS OWNED BY WP Ultimate
 * CSV Importer, WP Ultimate CSV Importer DISCLAIMS THE WARRANTY OF NON
 * INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * WP Ultimate CSV Importer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public
 * License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program; if not, see http://www.gnu.org/licenses or write
 * to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA 02110-1301 USA.
 *
 * You can contact Smackcoders at email address info@smackcoders.com.
 *
 * The interactive user interfaces in original and modified versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * WP Ultimate CSV Importer copyright notice. If the display of the logo is
 * not reasonably feasible for technical reasons, the Appropriate Legal
 * Notices must display the words
 * "Copyright Smackcoders. 2014. All rights reserved".
 ********************************************************************************/

require_once(WP_CONST_ULTIMATE_CSV_IMP_DIRECTORY . 'lib/skinnymvc/core/base/SkinnyBaseActions.php');
require_once(WP_CONST_ULTIMATE_CSV_IMP_DIRECTORY . 'lib/skinnymvc/core/SkinnyActions.php');
$skinnyObj = new CallWPImporterObj();
$curr_action = $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['selectedImporter'];
$importedAs = Null;
$inserted_post_count = 0;
$noofrecords = '';
if ($curr_action != 'post' && $curr_action != 'page' && $curr_action != 'custompost') {
	require_once(plugin_dir_path(__FILE__) . '../modules/' . $curr_action . '/actions/actions.php');
}
if ($curr_action == 'post' || $curr_action == 'page' || $curr_action == 'custompost') {
	$importObj = new WPImporter_includes_helper();
	if ($curr_action == 'post') {
		$importedAs = 'Post';
	}
	if ($curr_action == 'page') {
		$importedAs = 'Page';
	}
	if ($curr_action == 'custompost') {
		$importedAs = 'Custom Post';
	}

} elseif ($curr_action == 'eshop') {
	$importObj = new EshopActions();
} elseif ($curr_action == 'wpcommerce') {
	$importObj = new WpcommerceActions();
} elseif ($curr_action == 'woocommerce') {
	$importObj = new WooCommerceActions();
} elseif ($curr_action == 'users') {
	$importObj = new UsersActions();
	if ($curr_action == 'users') {
		$importedAs = 'Users';
	}
} elseif ($curr_action == 'categories') {
	$importObj = new CategoriesActions();
} elseif ($curr_action == 'customtaxonomy') {
	$importObj = new CustomTaxonomyActions();
} elseif ($curr_action == 'comments') {
	$importObj = new CommentsActions();
	if ($curr_action == 'comments') {
		$importedAs = 'Comments';
	}
}


$limit = $_POST['postdata']['limit'];
$totRecords = $_POST['postdata']['totRecords'];
$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['importlimit'] = $_POST['postdata']['importlimit'];
$count = $_POST['postdata']['importlimit'];
$requested_limit = $_POST['postdata']['importlimit'];
$tmpCnt = $_POST['postdata']['tmpcount'];

if ($count < $totRecords) {
	$count = $tmpCnt + $count;
	if ($count > $totRecords) {
		$count = $totRecords;
	}
} else {
	$count = $totRecords;
}
$resultArr = array();
$filename = $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['uploadedFile'];
$delim = $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['select_delimeter'];
$resultArr = $skinnyObj->csv_file_data($filename, $delim);
#print('<pre>'); print_r($resultArr); die;
if ($_POST['postdata']['dupTitle']) {
	$importObj->titleDupCheck = $_POST['postdata']['dupTitle'];
}
if ($_POST['postdata']['dupContent']) {
	$importObj->conDupCheck = $_POST['postdata']['dupContent'];
}
$csv_rec_count = $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['h2'];
for ($i = $limit; $i < $count; $i++) {
	$colCount = count($resultArr[$i]);
	$_SESSION['SMACK_SKIPPED_RECORDS'] = $i;
	foreach($resultArr[$i] as $resultKey => $resultVal) {
		$to_be_import_rec[] = $resultVal;
	}
	$importObj->processDataInWP($to_be_import_rec, $_SESSION['SMACK_MAPPING_SETTINGS_VALUES'], $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']);
	$limit++;
}
if ($limit >= $totRecords) {
	$dir = $skinnyObj->getUploadDirectory();
	$skinnyObj->deletefileafterprocesscomplete($dir);
}
if ($importObj->insPostCount != 0 || $importObj->dupPostCount != 0 || $importObj->updatedPostCount != 0) {
	if (!isset($_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['insPostCount'])) {
		$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['insPostCount'] = 0;
	}
	if (!isset($_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['dupPostCount'])) {
		$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['dupPostCount'] = 0;
	}
	if (!isset($_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['updatedPostCount'])) {
		$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['updatedPostCount'] = 0;
	}
	if (!isset($importObj->capturedId)) {
		$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['captureId'] = 0;
	}
	$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['insPostCount'] = $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['insPostCount'] + $importObj->insPostCount;
	$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['dupPostCount'] = $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['dupPostCount'] + $importObj->dupPostCount;
	$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['updatedPostCount'] = $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['updatedPostCount'] + $importObj->updatedPostCount;
	if (isset($importObj->capturedId)) {
		$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['captureId'] = $importObj->capturedId;
	}
}
if ($totRecords <= ($_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['insPostCount'] + $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['dupPostCount'] + $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['updatedPostCount'])) {
	if (!isset($_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['captureId'])) {
		$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['captureId'] = 0;
	}
	$inserted_post_count = $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['insPostCount'];
	if ($inserted_post_count != 0) {
		$importObj->addStatusLog($_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['insPostCount'], $importedAs);
	}
	if ($inserted_post_count != 0) {
		$importObj->addPieChartEntry($importedAs, $inserted_post_count);
		$inserted_post_count = 0;
	}
	$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['insPostCount'] = 0;
	$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['dupPostCount'] = 0;
	$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['updatedPostCount'] = 0;
	$_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['captureId'] = 0;
	unset($_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['insPostCount']);
	unset($_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['dupPostCount']);
	unset($_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['updatedPostCount']);
	unset($_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['captureId']);
}

if ($curr_action == 'users') {
	echo "<div style='margin-left:7px;'>";
	if (($limit == $requested_limit) && ($limit <= $count)) {
		echo "<div style='margin-left:3px;'>Chosen server request is " . $count . " .</div><br>";
	}
	echo "[" . date('h:m:s') . "] - No of user(s) Skipped - " . $importObj->dupPostCount . ".<br>";
	echo "[" . date('h:m:s') . "] - No of user(s) Inserted - " . $importObj->insPostCount . '.<br>';
	if ($limit == $totRecords) {
		echo "<br><div style='margin-left:3px;'>Import successfully completed!.</div>";
	}
	echo "</div>";
} elseif ($curr_action == 'comments') {
	echo "<div style='margin-left:7px;'>";
	if (($limit == $requested_limit) && ($limit <= $count)) {
		echo "<div style='margin-left:3px;'>Chosen server request is " . $count . " .</div><br>";
	}
	echo "[" . date('h:m:s') . "] - No of comment(s) Skipped - " . $importObj->dupPostCount . '.<br>';
	echo "[" . date('h:m:s') . "] - No of comment(s) Inserted - " . $importObj->insPostCount . ".<br>";
	if ($limit == $totRecords) {
		echo "<br><div style='margin-left:3px;'>Import successfully completed!.</div>";
	}
	echo "</div>";
} elseif ($curr_action == 'customtaxonomy') {
	echo "<div style='margin-left:7px;'>";
	if (($limit == $requested_limit) && ($limit <= $count)) {
		echo "<div style='margin-left:3px;'>Chosen server request is " . $count . " .</div><br>";
	}
	echo "[" . date('h:m:s') . "] - No of customtaxonomies Skipped - " . $importObj->dupPostCount . ".<br>";
	echo "[" . date('h:m:s') . "] - No of customtaxonomies Inserted - " . $importObj->insPostCount . ".<br>";
	if ($limit == $totRecords) {
		echo "<br><div style='margin-left:3px;'>Import successfully completed!.</div>";
	}
	echo "</div>";
} elseif ($curr_action == 'categories') {
	echo "<div style='margin-left:7px;'>";
	if (($limit == $requested_limit) && ($limit <= $count)) {
		echo "<div style='margin-left:3px;'>Chosen server request is " . $count . " .</div><br>";
	}
	echo "[" . date('h:m:s') . "] - No of categories Skipped - " . $importObj->dupPostCount . '.<br>';
	echo "[" . date('h:m:s') . "] - No of categories Inserted - " . $importObj->insPostCount . '.<br>';
	if ($limit == $totRecords) {
		echo "<br><div style='margin-left:3px;'>Import successfully completed!.</div>";
	}
	echo "</div>";
} elseif ($curr_action == 'post') {
	echo "<div style='margin-left:7px;'>";
	if (($limit == $requested_limit) && ($limit <= $count)) {
		echo "<div style='margin-left:3px;'>Chosen server request is " . $count . " .</div><br>";
	}
	echo "[" . date('h:m:s') . "] - No of post(s) Skipped - " . $importObj->dupPostCount . '.<br>';
	echo "[" . date('h:m:s') . "] - No of post(s) Inserted - " . $importObj->insPostCount . '.<br>';
	echo "[" . date('h:m:s') . "] - No of post(s) are assigned as admin - " . $importObj->noPostAuthCount . ".<br>";
	if ($limit == $totRecords) {
		echo "<br><div style='margin-left:3px;'>Import successfully completed!.</div>";
	}
	echo "</div>";
} elseif ($curr_action == 'page') {
	echo "<div style='margin-left:7px;'>";
	if (($limit == $requested_limit) && ($limit <= $count)) {
		echo "<div style='margin-left:3px;'>Chosen server request is " . $count . " .</div><br>";
	}
	echo "[" . date('h:m:s') . "] - No of page(s) Skipped - " . $importObj->dupPostCount . '.<br>';
	echo "[" . date('h:m:s') . "] - No of page(s) Inserted - " . $importObj->insPostCount . '.<br>';
	echo "[" . date('h:m:s') . "] - No of page(s) are assigned as admin - " . $importObj->noPostAuthCount . '.<br>';
	if ($limit == $totRecords) {
		echo "<br><div style='margin-left:3px;'>Import successfully completed!.</div>";
	}
	echo "</div>";
} elseif ($curr_action == 'custompost') {
	$customposttype = $_SESSION['SMACK_MAPPING_SETTINGS_VALUES']['custompostlist'];
	echo "<div style='margin-left:7px;'>";
	if (($limit == $requested_limit) && ($limit <= $count)) {
		echo "<div style='margin-left:3px;'>Chosen server request is " . $count . " .</div><br>";
	}
	echo "[" . date('h:m:s') . "] - No of " . $customposttype . " Skipped - " . $importObj->dupPostCount . '.<br>';
	echo "[" . date('h:m:s') . "] - No of " . $customposttype . " Inserted - " . $importObj->insPostCount . '.<br>';
	echo "[" . date('h:m:s') . "] - No of " . $customposttype . " are assigned as admin - " . $importObj->noPostAuthCount . ".<br>";
	if ($limit == $totRecords) {
		echo "<br><div style='margin-left:3px;'>Import successfully completed!.</div>";
	}
	echo "</div>";
}
foreach ($_SESSION['SMACK_MAPPING_SETTINGS_VALUES'] as $key => $value) {
	for ($j = 0; $j < $csv_rec_count; $j++) {
		if ($key == 'mapping' . $j) {
			$mapArr[$j] = $value;
		}
	}
}
