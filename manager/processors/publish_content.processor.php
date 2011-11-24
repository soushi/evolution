<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('save_document')||!$modx->hasPermission('publish_document')) {
	$e->setError(3);
	$e->dumpError();	
}

$id = $_REQUEST['id'];

// check permissions on the document
include_once "./processors/user_documents_permissions.class.php";
$udperms = new udperms();
$udperms->user = $modx->getLoginUserID();
$udperms->document = $id;
$udperms->role = $_SESSION['mgrRole'];

if(!$udperms->checkPermissions()) {
	include "header.inc.php";
	?><div class="sectionHeader"><?php echo $_lang['access_permissions']; ?></div>
	<div class="sectionBody">
	<p><?php echo $_lang['access_permission_denied']; ?></p>
	<?php
	include("footer.inc.php");
	exit;	
}

// update the document
$field['published']   = 1;
$field['pub_date']    = 0;
$field['unpub_date']  = 0;
$field['editedby']    = $modx->getLoginUserID();
$field['editedon']    = time();
$field['publishedby'] = $modx->getLoginUserID();
$field['publishedon'] = time();
$tbl_site_content     = $modx->getFullTableName('site_content');
$rs = $modx->db->update($field,$tbl_site_content,"id={$id}");
if(!$rs)
{
	echo "An error occured while attempting to publish the document.";
}

// invoke OnDocPublished  event
$modx->invokeEvent("OnDocPublished",array("docid"=>$id));	

include_once "cache_sync.class.processor.php";
$sync = new synccache();
$sync->setCachepath("../assets/cache/");
$sync->setReport(false);
$sync->emptyCache(); // first empty the cache
$pid = $modx->db->getValue($modx->db->select('parent',$tbl_site_content,"id='{$id}'"));
$page = (isset($_GET['page'])) ? "&page={$_GET['page']}" : '';
if($pid!=='0') $header="Location: index.php?r=1&a=3&id={$pid}&tab=0{$page}";
else           $header="Location: index.php?a=2&r=1";
header($header);
?>