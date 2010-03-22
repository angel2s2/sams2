<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
// SHOW TABLE STATUS WHERE name='squidusers';
//SHOW VARIABLES where variable_name='character_set_database';
//show server_encoding;
class IMPORTUSERS
{
  var $sams1charset;
  var $sams2charset;
  var $pgcharset;
  var $encode;
  var $groupname = array();
  var $trangename = array();
  var $trange = array();
  var $trangecount;
  
  var $urllistname=array();
  var $urllistid2=array();
  var $urllistcount;
  var $groupid=array();
  var $groupid2=array();
  var $groupcount;
  var $groupcount2;
  var $shablonname=array();
  var $shablonid=array();
  var $shablonid2=array();
  var $shabloncount;
  var $shabloncount2;
  var $DB;
  var $oldDB;
  var $DBcharset;
  var $oldDBcharset;

function importurllists()
{
  global $SAMSConf;
  global $USERConf;


  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="";
	if($SAMSConf->DB_ENGINE=="MySQL")
		$DBNAME="samsdb.";

	$shabloncount=0;
	echo "<H2>$configbuttom_3_import_importurllists_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$redir_1\n";
	echo "<TH>\n";
	$this->oldDB->samsdb_query_value("SELECT * FROM squidctrl.redirect ");
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		echo "<TR><TD><B>$row[name]</B><TD> added<BR>";
		$this->DB->samsdb_query("INSERT INTO " .$DBNAME. "redirect (s_name,s_type) VALUES( '$row[name]', '$row[type]')");
	}
	$this->oldDB->free_samsdb_query();
	$i=0;
	$this->urllistcount=$this->DB->samsdb_query_value("SELECT * FROM " .$DBNAME. "redirect ");
	while($row=$this->DB->samsdb_fetch_array())
	{
		$this->urllistname[$i]=$row['s_name'];
		$this->urllistid2[$i]=$row['s_redirect_id'];
		$i++;
	}
	$this->DB->free_samsdb_query();
	$this->oldDB->samsdb_query_value("SELECT urls.*,redirect.name as rname FROM squidctrl.urls LEFT JOIN squidctrl.redirect ON urls.type=redirect.filename ");
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$index=array_search($row['rname'], $this->urllistname);
			$this->DB->samsdb_query("INSERT INTO " .$DBNAME. "url ( s_redirect_id , s_url ) VALUES ( '".$this->urllistid2[$index]."', '$row[url]' )");
	}
	$this->oldDB->free_samsdb_query();
	echo "</TABLE>\n";

}


function importgroups()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="";
	if($SAMSConf->DB_ENGINE=="MySQL")
		$DBNAME="samsdb.";

	$this->groupcount=0;
	$this->oldDB->samsdb_query_value("SELECT * FROM sams ");
	$row=$this->oldDB->samsdb_fetch_array();
	
	$this->oldDB->samsdb_query_value("SELECT * FROM groups ");
	echo "<H2>$configbuttom_3_import_importgroups_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$grouptray_NewGroupForm_2\n";
	echo "<TH>\n";
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$this->groupname[] ="$row[nick]";
		$this->groupid[]="$row[name]";

		if($row['nick']!="Administrators"&&$row['nick']!="Users")
		{

			$GROUPNAME=$this->groupname[$this->groupcount];
			echo "<TR><TD><B>$GROUPNAME</B>";

			$GROUPNAME5=$GROUPNAME;
			//$GROUPNAME5 = substr_replace ( $GROUPNAME, "", 1 );

			$QUERY="INSERT INTO " .$DBNAME. "sgroup ( s_name ) VALUES ('".$GROUPNAME."') ";
			$this->DB->samsdb_query($QUERY);
			echo "<TD>added";
		}
		$this->groupcount++;
	}
  $this->oldDB->free_samsdb_query();
  echo "</TABLE>\n";
}

function importtimerange()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="";
	if($SAMSConf->DB_ENGINE=="MySQL")
		$DBNAME="samsdb.";

	$this->oldDB->samsdb_query_value("select days,shour,smin,ehour,emin from shablons group by days,shour,smin,ehour,emin");
	echo "<H2>$configbuttom_3_import_importtimerange_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$shablonbuttom_1_prop_UpdateShablonForm_14\n";
	echo "<TH>$shablonbuttom_1_prop_UpdateShablonForm_13\n";
	echo "<TH>\n";
	$this->trangecount=0;
	while($row=$this->oldDB->samsdb_fetch_array())
	{
//		$this->trangename[] ="$this->trangecount";
//		$this->trange[$this->trangecount] = $row['days']." ".$row['shour'].".".$row['smin'].":".$row['ehour'].".".$row['emin'];

			echo "<TR><TD>".$row['days'];
			echo "<TD>".$row['shour'].".".$row['smin'].":".$row['ehour'].".".$row['emin'];

			$QUERY="INSERT INTO ".$DBNAME."timerange( s_name, s_days, s_timestart, s_timeend ) VALUES ( 'import_".$this->trangecount."', '".$row['days']."', '".$row['shour'].":".$row['smin'].":00','".$row['ehour'].":".$row['emin'].":00' )";
			$this->DB->samsdb_query($QUERY);
			echo "<TD>added";
		$this->trangecount++;
	}
  $this->oldDB->free_samsdb_query();
  echo "</TABLE>\n";
}

function importshablons()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="";
	if($SAMSConf->DB_ENGINE=="MySQL")
		$DBNAME="samsdb.";

	$shabloncount=0;
	$this->oldDB->samsdb_query_value("SELECT * FROM shablons ");
	echo "<H2>$configbuttom_3_import_importshablons_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$shablonnew_NewShablonForm_2\n";
	echo "<TH>\n";
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$this->shablonname[] ="$row[nick]";
		$this->shablonid[]="$row[name]";
		if($row['clrdate']=="0000-00-00")
			$clrdate="1980-01-01";
		else
			$clrdate=$row['clrdate'];
		//print("$row[nick]: $clrdate <BR>");
		if($row['name']!="default")
		{
			echo "<TR><TD><B>$row[nick]</B>";
			$this->DB->samsdb_query("INSERT INTO " .$DBNAME. "shablon ( s_name, s_auth, s_quote, s_period, s_clrdate, s_alldenied) VALUES ('$row[nick]', '$row[auth]', '$row[traffic]', '$row[period]', '$clrdate', '$row[alldenied]' ) ");


			$this->DB->samsdb_query_value("SELECT s_shablon_id FROM ".$DBNAME."shablon WHERE s_name='$row[nick]'");
	                while($row2=$this->DB->samsdb_fetch_array())
                          $new_shablon_id=$row2['s_shablon_id'];

			$this->DB->samsdb_query("INSERT INTO " .$DBNAME. "delaypool ( s_name, s_class, s_agg1, s_ind1) VALUES ('$row[nick]', '2', '$row[shablonpool]', '$row[userpool]' ) ");


			$this->DB->samsdb_query_value("SELECT s_pool_id FROM ".$DBNAME."delaypool WHERE s_name='$row[nick]'");
	                while($row2=$this->DB->samsdb_fetch_array())
                          $new_pool_id=$row2['s_shablon_id'];

			$this->DB->samsdb_query("INSERT INTO " .$DBNAME. "d_link_s ( s_pool_id, s_shablon_id, s_negative) VALUES ('$new_pool_id', '$new_shablon_id', '0') ");

			$this->DB->samsdb_query_value("SELECT s_trange_id FROM ".$DBNAME."timerange WHERE s_days='".$row['days']."' AND s_timestart='".$row['shour'].":".$row['smin'].":00' AND s_timeend='".$row['ehour'].":".$row['emin'].":00'");
	                while($row2=$this->DB->samsdb_fetch_array())
                          $new_trange_id=$row2['s_trange_id'];

			$this->DB->samsdb_query("INSERT INTO " .$DBNAME. "sconfig_time ( s_shablon_id, s_trange_id) VALUES ('$new_shablon_id', '$new_trange_id') ");

			echo "<TD>added";
		}
		$this->shabloncount++;
	}
  $this->oldDB->free_samsdb_query();
  echo "</TABLE>";

}

function seturllists()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="";
//	if($SAMSConf->DB_ENGINE=="MySQL")
//		$DBNAME="samsdb.";

	$shabloncount=0;
	$this->oldDB->samsdb_query_value("select sconfig.sname, sconfig.set, shablons.name, shablons.nick, redirect.name as gname from sconfig left join shablons on shablons.name=sconfig.sname left join redirect on redirect.filename=sconfig.set ");
	echo "<H2>$configbuttom_3_import_importshablons_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$shablonnew_NewShablonForm_2\n";
	echo "<TH>$redir_addredirectform2\n";
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		if($row['sname'] == $row['name'])
		{
			echo "<TR>\n";
			echo "<TD>$row[nick]\n";
			echo "<TD>$row[gname]\n";

			$QUERY="SELECT s_shablon_id, s_name FROM ".$SAMSConf->SAMSDB.".shablon WHERE s_name='$row[nick]'";
			$num_rows_shablon=$this->DB->samsdb_query_value($QUERY);
			while($row2=$this->DB->samsdb_fetch_array())
			{
				$shablonid=$row2['s_shablon_id'];
			}
			$QUERY="SELECT s_redirect_id, s_name FROM ".$SAMSConf->SAMSDB.".redirect WHERE s_name='$row[gname]'";
			$num_rows_redirect=$this->DB->samsdb_query_value($QUERY);
			while($row2=$this->DB->samsdb_fetch_array())
			{
				$redirectid=$row2['s_redirect_id'];
			}
			if($num_rows_shablon>0 && $num_rows_redirect>0)
			{
				$QUERY="INSERT INTO ".$SAMSConf->SAMSDB.".sconfig ( s_shablon_id, s_redirect_id ) VALUES ('$shablonid', '$redirectid') ";
				$num_rows=$this->DB->samsdb_query($QUERY);
			}
		}
	}
	echo "</table>\n";
}

function importsamsusers()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	$DBNAME="";
	if($SAMSConf->DB_ENGINE=="MySQL")
		$DBNAME="samsdb.";


	$groupcount2=0;
	for($i=0;$i<$this->groupcount;$i++)
	{
	$this->DB->samsdb_query_value("SELECT s_group_id FROM " .$DBNAME. "sgroup WHERE s_name='".$this->groupname[$i]."' ");
	while($row=$this->DB->samsdb_fetch_array())
		{
			$this->groupid2[$i]=$row['s_group_id'];
			$this->groupcount2++;
		}
  	$this->DB->free_samsdb_query();
	}

	$this->shabloncount2=0;
	for($i=0;$i<$this->shabloncount;$i++)
	{
	$this->DB->samsdb_query_value("SELECT s_shablon_id FROM " .$DBNAME. "shablon WHERE s_name='".$this->shablonname[$i]."' ");
	while($row=$this->DB->samsdb_fetch_array())
		{
			$this->shablonid2[$i]=$row['s_shablon_id'];
			$this->shabloncount2++;
		}
  	$this->DB->free_samsdb_query();
	}
	$this->oldDB->samsdb_query_value("SELECT * FROM squidusers ORDER BY nick");
	echo "<H2>$configbuttom_3_import_importsamsusers_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$grouptray_NewGroupForm_4\n";
	echo "<TH>$grouptray_NewGroupForm_8\n";
	echo "<TH>\n";
	while($row=$this->oldDB->samsdb_fetch_array())
	{

		$sindex=array_search($row['shablon'], $this->shablonid);
		$gindex=array_search($row['group'], $this->groupid);
		if($row['family']!="") 
			$s_family = $row['family'];
		else
			$s_family = ".";
			
		if($row['name']!="") 
			$s_name = $row['name'];
		else
			$s_name = ".";
		if($row['soname']!="") 
			$s_soname = $row['soname'];
		else
			$s_soname = ".";
		if($row['ip']!="") 
			$s_ip = $row['ip'];
		else
			$s_ip = "....";
		echo "<TR><TD><B>$row[nick]</B><TD> $s_family $s_name <BR>";
		$str="(  s_group_id, s_shablon_id, s_nick, s_family, s_name, s_soname, s_domain, s_quote, s_size, s_hit, s_enabled, s_ip, s_passwd, s_gauditor, s_autherrorc, s_autherrort )";
		$values="( '".$this->groupid2[$gindex]."', '".$this->shablonid2[$sindex]."', '$row[nick]', '$s_family', '$s_name', '$s_soname', '$row[domain]', '$row[quotes]', '$row[size]', '$row[hit]', '$row[enabled]', '$s_ip', '$row[passwd]', '$row[gauditor]',  '$row[autherrorc]', '$row[autherrort]' )";
		$this->DB->samsdb_query("INSERT INTO " .$DBNAME. "squiduser $str VALUES $values ");
		echo "<TD>added\n";
		$count++;
	}
  $this->oldDB->free_samsdb_query();
  echo "</TABLE>\n";

}


function IMPORTUSERS($hostname, $username, $pass)
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

 $this->DB=new SAMSDB();
 $this->oldDB=new CREATESAMSDB("MySQL", "0", $hostname, $username, $pass, "squidctrl", "0");

 $this->oldDB->samsdb_query_value("SELECT lang FROM globalsettings");
 $row=$this->oldDB->samsdb_fetch_array();


// SHOW TABLE STATUS WHERE name='squidusers';
//SHOW VARIABLES where variable_name='character_set_database';
//show server_encoding;


 $this->oldDB->samsdb_query_value("SHOW VARIABLES WHERE variable_name='character_set_database'");
 $row=$this->oldDB->samsdb_fetch_array();
 $this->oldDBcharset=$row[0];
 $this->oldDB->free_samsdb_query();

 $this->DB->samsdb_query_value("show server_encoding");
 $row=$this->DB->samsdb_fetch_array();
 $this->DBcharset=$row[0];
 $this->DB->free_samsdb_query();

 $this->pgcharset=pg_client_encoding($this->DB->link);

 if($SAMSConf->DB_ENGINE=="PostgreSQL"&&$this->sams1charset!=$this->pgcharset)
 {
	if($this->sams1charset=="KOI8-R")
	{
		pg_set_client_encoding("KOI8");
	}
 }

}

}

function importdata()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{       exit;     }

 if(isset($_GET["importusers"])) $importusers=$_GET["importusers"];
 if(isset($_GET["importgroups"])) $importgroups=$_GET["importgroups"];
 if(isset($_GET["importurllists"])) $importurllists=$_GET["importurllists"];
 if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
 if(isset($_GET["username"])) $username=$_GET["username"];
 if(isset($_GET["pass"])) $pass=$_GET["pass"];

   $IMP=new IMPORTUSERS($hostname, $username, $pass);

  if($importusers=="on")
	{
		$IMP->importgroups();
		echo "<BR>";
		$IMP->importtimerange();
		echo "<BR>";
		$IMP->importshablons();
		echo "<BR>";
		$IMP->importsamsusers();
		echo "<BR>";
	}
  if($importurllists=="on")
	{
		$IMP->importurllists();
	}

  if($importusers=="on" && $importurllists=="on")
	{
		$IMP->seturllists();
	}
  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
}

function importdataform()
{
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);
  PageTop("shablon.jpg","$configbuttom_3_import_importdataform_1 ");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/importfromsams1.html\">$documentation</A>");
  print("<P>\n");

			print("<FORM NAME=\"createdatabase\" ACTION=\"main.php\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"importdata\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"configbuttom_3_import.php\">\n");
			print("<TABLE WIDTH=\"90%\">\n");
			print("<TR><TD ALIGN=RIGHT>DB Hostname: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"hostname\" value=\"localhost\">\n");
			print("<TR><TD ALIGN=RIGHT>DB login: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"username\" value=\"$dbadmin\">\n");
			print("<TR><TD ALIGN=RIGHT>DB password: <TD ALIGN=LEFT><INPUT TYPE=\"PASSWORD\" NAME=\"pass\">\n");
			print("<TR><TD ALIGN=RIGHT>$configbuttom_3_import_importdataform_2: <TD ALIGN=LEFT><INPUT TYPE=\"CHECKBOX\" NAME=\"importusers\">\n");
			print("<TR><TD ALIGN=RIGHT>$configbuttom_3_import_importdataform_3: <TD ALIGN=LEFT><INPUT TYPE=\"CHECKBOX\" NAME=\"importurllists\">\n");
			print("</TABLE>\n");

			printf("<BR><CENTER>");
			print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$configbuttom_3_import_importdataform_5\">\n");
			print("</FORM>\n");
}



function configbuttom_3_import()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       GraphButton("main.php?show=exe&function=importdataform&filename=configbuttom_3_import.php",
	               "basefrm","importdb_32.jpg","importdb_48.jpg","  import data from sams ver.1 database  ");
    }
}

?>
