<?php
/*
 
  ----------------------------------------------------------------------
GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2004 by the INDEPNET Development Team.
 
 http://indepnet.net/   http://glpi.indepnet.org
 ----------------------------------------------------------------------
 LICENSE

This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
 
// Based on:
// IRMA, Information Resource-Management and Administration
// Christian Bauer 
// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

include ("_relpos.php");
// FUNCTIONS Printers 
//fonction imprimantes

function titlePrinters(){
           GLOBAL  $lang,$HTMLRel;
           
           echo "<div align='center'><table border='0'><tr><td>";

           echo "<img src=\"".$HTMLRel."pics/printer.png\" alt='".$lang["printers"][0]."' title='".$lang["printers"][0]."'></td><td><a  class='icon_consol' href=\"printers-add-select.php\"><b>".$lang["printers"][0]."</b></a>";


           echo "</td></tr></table></div>";
}



function searchFormPrinters($field="",$phrasetype= "",$contains="",$sort= "",$deleted="") {
	// Print Search Form
	
	GLOBAL $cfg_install, $cfg_layout, $layout, $lang,$HTMLRel;

	$option["printer.name"]				= $lang["printers"][5];
	$option["printer.ID"]				= $lang["printers"][19];
	$option["glpi_dropdown_locations.name"]			= $lang["printers"][6];
	$option["glpi_type_printers.name"]				= $lang["printers"][9];
	$option["printer.serial"]			= $lang["printers"][10];
	$option["printer.otherserial"]		= $lang["printers"][11]	;
	$option["printer.comments"]			= $lang["printers"][12];
	$option["printer.contact"]			= $lang["printers"][8];
	$option["printer.contact_num"]		= $lang["printers"][7];
	$option["printer.date_mod"]			= $lang["printers"][16];
	$option["glpi_networking_ports.ifaddr"] = $lang["networking"][14];
	$option["glpi_networking_ports.ifmac"] = $lang["networking"][15];
	$option["glpi_dropdown_netpoint.name"]			= $lang["networking"][51];
	$option["glpi_enterprises.name"]			= $lang["common"][5];

	echo "<form method='get' action=\"".$cfg_install["root"]."/printers/printers-search.php\">";
	echo "<div align='center'><table  width='750' class='tab_cadre'>";
	echo "<tr><th colspan='3'><b>".$lang["search"][0].":</b></th></tr>";
	echo "<tr class='tab_bg_1'>";
	echo "<td align='center'>";
	echo "<input type='text' size='15' name=\"contains\" value=\"". $contains ."\" >";
	echo "&nbsp;";
	echo $lang["search"][10]."&nbsp;";
	
	echo "<select name=\"field\" size='1'>";
        echo "<option value='all' ";
	if($field == "all") echo "selected";
	echo ">".$lang["search"][7]."</option>";
        reset($option);
	foreach ($option as $key => $val) {
		echo "<option value=\"".$key."\""; 
		if($key == $field) echo "selected";
		echo ">". $val ."</option>\n";
	}
	echo "</select>&nbsp;";
	
	/*
	echo $lang["search"][1];
	echo "&nbsp;<select name='phrasetype' size='1' >";
	echo "<option value='contains'";
	if($phrasetype == "contains") echo "selected";
	echo ">".$lang["search"][2]."</option>";
	echo "<option value='exact'";
	if($phrasetype == "exact") echo "selected";
	echo ">".$lang["search"][3]."</option>";
	echo "</select>";
	*/
	
	echo $lang["search"][4];
	echo "&nbsp;<select name='sort' size='1'>";
	reset($option);
	foreach ($option as $key => $val) {
		echo "<option value=\"".$key."\"";
		if($key == $sort) echo "selected";
		echo ">".$val."</option>\n";
	}
	echo "</select> ";
	echo "</td><td><input type='checkbox' name='deleted' ".($deleted=='Y'?" checked ":"").">";
	echo "<img src=\"".$HTMLRel."pics/showdeleted.png\" alt='".$lang["common"][3]."' title='".$lang["common"][3]."'>";
	echo "</td><td width='80' align='center' class='tab_bg_2'>";
	echo "<input type='submit' value=\"".$lang["buttons"][0]."\" class='submit'>";
	echo "</td></tr></table></div></form>";
}


function showPrintersList($target,$username,$field,$phrasetype,$contains,$sort,$order,$start,$deleted) {

	// Lists Printers

	GLOBAL $cfg_install, $cfg_layout, $cfg_features, $lang, $HTMLRel;

	$db = new DB;
	// Build query
	if($field=="all") {
		$where = " (";
		$fields = $db->list_fields("glpi_printers");
		$columns = $db->num_fields($fields);
		
		for ($i = 0; $i < $columns; $i++) {
			if($i != 0) {
				$where .= " OR ";
			}
			$coco = mysql_field_name($fields, $i);

			if($coco == "location") {
				$where .= " glpi_dropdown_locations.name LIKE '%".$contains."%'";
			}
			elseif($coco == "type") {
				$where .= " glpi_type_printers.name LIKE '%".$contains."%'";
			}
			else {
   				$where .= "printer.".$coco . " LIKE '%".$contains."%'";
			}
		}
		$where .= " OR glpi_networking_ports.ifaddr LIKE '%".$contains."%'";
		$where .= " OR glpi_networking_ports.ifmac LIKE '%".$contains."%'";
		$where .= " OR glpi_dropdown_netpoint.name LIKE '%".$contains."%'";
		$where.=" OR glpi_enterprises.name LIKE '%".$contains."%'";
		$where .= ")";
	}
	else {
		if ($phrasetype == "contains") {
			$where = "($field LIKE '%".$contains."%')";
		}
		else {
			$where = "($field LIKE '".$contains."')";
		}
	}

	if (!$start) {
		$start = 0;
	}
	if (!$order) {
		$order = "ASC";
	}
	$query = "select DISTINCT printer.ID from glpi_printers as printer LEFT JOIN glpi_dropdown_locations on printer.location=glpi_dropdown_locations.ID ";
	$query .= "LEFT JOIN glpi_type_printers on printer.type = glpi_type_printers.ID ";
	$query .= "LEFT JOIN glpi_networking_ports on (printer.ID = glpi_networking_ports.on_device AND  glpi_networking_ports.device_type='3')";	
	$query .= "LEFT JOIN glpi_dropdown_netpoint on (glpi_dropdown_netpoint.ID = glpi_networking_ports.netpoint)";	
	$query.= " LEFT JOIN glpi_enterprises ON (glpi_enterprises.ID = printer.FK_glpi_enterprise ) ";
	$query .= "where $where AND printer.deleted='$deleted' AND printer.is_template = '0' ORDER BY $sort $order";
	
//	echo $query;
	// Get it from database	
	if ($result = $db->query($query)) {
		$numrows = $db->numrows($result);

		// Limit the result, if no limit applies, use prior result
		if ($numrows>$cfg_features["list_limit"]) {
			$query_limit = $query ." LIMIT $start,".$cfg_features["list_limit"]." ";
			$result_limit = $db->query($query_limit);
			$numrows_limit = $db->numrows($result_limit);

		} else {
			$numrows_limit = $numrows;
			$result_limit = $result;
		}


		if ($numrows_limit>0) {
			// Produce headline
			echo "<center><table class='tab_cadre'><tr>";

			// Name
			echo "<th>";
			if ($sort=="printer.name") {
				echo "<img src=\"".$HTMLRel."pics/puce-down.gif\" alt='' title=''>";
			}
			echo "<a href=\"$target?field=$field&phrasetype=$phrasetype&contains=$contains&sort=printer.name&order=ASC&start=$start\">";
			echo $lang["printers"][5]."</a></th>";

			// Manufacturer		
			echo "<th>";
			if ($sort=="glpi_enterprises.name") {
				echo "<img src=\"".$HTMLRel."pics/puce-down.gif\" alt='' title=''>";
			}
			echo "<a href=\"$target?field=$field&phrasetype=$phrasetype&contains=$contains&sort=glpi_enterprises.name&order=ASC&start=$start\">";
			echo $lang["common"][5]."</a></th>";
			
			// Location			
			echo "<th>";
			if ($sort=="glpi_dropdown_locations.name") {
				echo "<img src=\"".$HTMLRel."pics/puce-down.gif\" alt='' title=''>";
			}
			echo "<a href=\"$target?field=$field&phrasetype=$phrasetype&contains=$contains&sort=glpi_dropdown_locations.name&order=ASC&start=$start\">";
			echo $lang["printers"][6]."</a></th>";

			// Type
			echo "<th>";
			if ($sort=="glpi_type_printers.name") {
				echo "<img src=\"".$HTMLRel."pics/puce-down.gif\" alt='' title=''>";
			}
			echo "<a href=\"$target?field=$field&phrasetype=$phrasetype&contains=$contains&sort=glpi_type_printers.name&order=ASC&start=$start\">";
			echo $lang["printers"][9]."</a></th>";

			// Last modified		
			echo "<th>";
			if ($sort=="printer.date_mod") {
				echo "<img src=\"".$HTMLRel."pics/puce-down.gif\" alt='' title=''>";
			}
			echo "<a href=\"$target?field=$field&phrasetype=$phrasetype&contains=$contains&sort=printer.date_mod&order=DESC&start=$start\">";
			echo $lang["printers"][16]."</a></th>";
	
			echo "</tr>";

			for ($i=0; $i < $numrows_limit; $i++) {
				$ID = $db->result($result_limit, $i, "ID");
				$printer = new Printer;
				$printer->getfromDB($ID);
				echo "<tr class='tab_bg_2'>";
				echo "<td><b>";
				echo "<a href=\"".$cfg_install["root"]."/printers/printers-info-form.php?ID=$ID\">";
				echo $printer->fields["name"]." (".$printer->fields["ID"].")";
				echo "</a></b></td>";
				echo "<td>". getDropdownName("glpi_enterprises",$printer->fields["FK_glpi_enterprise"]) ."</td>";
				echo "<td>". getDropdownName("glpi_dropdown_locations",$printer->fields["location"]) ."</td>";
				echo "<td>". getDropdownName("glpi_type_printers",$printer->fields["type"]) ."</td>";
				echo "<td>".$printer->fields["date_mod"]."</td>";
				echo "</tr>";
			}

			// Close Table
			echo "</table></center>";

			// Pager
			$parameters="field=$field&phrasetype=$phrasetype&contains=$contains&sort=$sort&order=$order";
			printPager($start,$numrows,$target,$parameters);

		} else {
			echo "<center><b>".$lang["printers"][17]."</b></center>";
			echo "<hr noshade>";
		}
	}
}


function showPrintersForm ($target,$ID,$withtemplate='') {

	GLOBAL $cfg_install, $cfg_layout, $lang,$HTMLRel;

	$printer = new Printer;

	$printer_spotted = false;

	if(empty($ID) && $withtemplate == 1) {
		if($printer->getEmpty()) $printer_spotted = true;
	} else {
		if($printer->getfromDB($ID)) $printer_spotted = true;
	}

	if($printer_spotted) {
		if(!empty($withtemplate) && $withtemplate == 2) {
			$template = "newcomp";
			$datestring = $lang["computers"][14].": ";
			$date = date("Y-m-d H:i:s");
		} elseif(!empty($withtemplate) && $withtemplate == 1) { 
			$template = "newtemplate";
			$datestring = $lang["computers"][14].": ";
			$date = date("Y-m-d H:i:s");
		} else {
			$datestring = $lang["computers"][11]." : ";
			$date = $printer->fields["date_mod"];
			$template = false;
		}


	echo "<div align='center'><form method='post' name='form' action=\"$target\">";
		if(strcmp($template,"newtemplate") === 0) {
			echo "<input type=\"hidden\" name=\"is_template\" value=\"1\" />";
		}

	echo "<table class='tab_cadre' cellpadding='2'>";

		echo "<tr><th align='center' >";
		if(!$template) {
			echo $lang["printers"][29].": ".$printer->fields["ID"];
		}elseif (strcmp($template,"newcomp") === 0) {
			echo $lang["printers"][28].": ".$printer->fields["tplname"];
		}elseif (strcmp($template,"newtemplate") === 0) {
			echo $lang["common"][6]."&nbsp;: <input type='text' name='tplname' value=\"".$printer->fields["tplname"]."\" size='20'>";
		}
		
		echo "</th><th  align='center'>".$datestring.$date;
		echo "</th></tr>";


	echo "<tr><td class='tab_bg_1' valign='top'>";

	// table identification
	echo "<table cellpadding='1px' cellspacing='0' border='0'>\n";
	echo "<tr><td>".$lang["printers"][5].":	</td>";
	echo "<td><input type='text' name='name' value=\"".$printer->fields["name"]."\" size='20'></td>";
	echo "</tr>";

	echo "<tr><td>".$lang["printers"][6].": 	</td><td>";
		dropdownValue("glpi_dropdown_locations", "location", $printer->fields["location"]);
	echo "</td></tr>";

	echo "<tr class='tab_bg_1'><td>".$lang["common"][5].": 	</td><td colspan='2'>";
		dropdownValue("glpi_enterprises","FK_glpi_enterprise",$printer->fields["FK_glpi_enterprise"]);
	echo "</td></tr>";
	
	echo "<tr><td>".$lang["printers"][7].":	</td>";
	echo "<td><input type='text' name='contact_num' value=\"".$printer->fields["contact_num"]."\" size='20'></td>";
	echo "</tr>";

	echo "<tr><td>".$lang["printers"][8].":	</td>";
	echo "<td><input type='text' name='contact' size='20' value=\"".$printer->fields["contact"]."\"></td>";
	echo "</tr>";
	if (!empty($ID)){
		echo "<tr><td>".$lang["reservation"][24].":</td><td><b>";
		showReservationForm(3,$ID);
		echo "</b></td></tr>";
	}
	echo "</table>"; // fin table indentification

	echo "</td>\n";	
	echo "<td class='tab_bg_1' valign='top'>";

	// table type,serial..
	echo "<table cellpadding='1px' cellspacing='0' border='0'>";

	echo "<tr><td>".$lang["printers"][9].": 	</td><td>";
		dropdownValue("glpi_type_printers", "type", $printer->fields["type"]);
	echo "</td></tr>";
		
	echo "<tr><td>".$lang["printers"][10].":	</td>";
	echo "<td><input type='text' name='serial' size='20' value=\"".$printer->fields["serial"]."\"></td>";
	echo "</tr>";

	echo "<tr><td>".$lang["printers"][11].":</td>";
	echo "<td><input type='text' size='20' name='otherserial' value=\"".$printer->fields["otherserial"]."\"></td>";
	echo "</tr>";

		echo "<tr><td>".$lang["printers"][18].": </td><td>";

		// serial interface?
		echo "<table border='0' cellpadding='2' cellspacing='0'><tr>";
		echo "<td>";
		if ($printer->fields["flags_serial"] == 1) {
			echo "<input type='checkbox' name='flags_serial' value='1' checked>";
		} else {
			echo "<input type='checkbox' name='flags_serial' value='1'>";
		}
		echo "</td><td>".$lang["printers"][14]."</td>";
		echo "</tr></table>";

		// parallel interface?
		echo "<table border='0' cellpadding='2' cellspacing='0'><tr>";
		echo "<td>";
		if ($printer->fields["flags_par"] == 1) {
			echo "<input type='checkbox' name='flags_par' value='1' checked>";
		} else {
			echo "<input type='checkbox' name='flags_par' value='1'>";
		}
		echo "</td><td>".$lang["printers"][15]."</td>";
		echo "</tr></table>";
		
		// USB ?
		echo "<table border='0' cellpadding='2' cellspacing='0'><tr>";
		echo "<td>";
		if ($printer->fields["flags_usb"] == 1) {
			echo "<input type='checkbox' name='flags_usb' value='1' checked>";
		} else {
			echo "<input type='checkbox' name='flags_usb' value='1'>";
		}
		echo "</td><td>".$lang["printers"][27]."</td>";
		echo "</tr></table>";
		
		// Ram ?
		echo "<tr><td>".$lang["printers"][23].":</td>";
		echo "<td><input type='text' size='12' name='ramSize' value=\"".$printer->fields["ramSize"]."\"></td>";
		echo "</tr>";

		echo "</td></tr>";

	echo "</table>";
	echo "</td>\n";	
	echo "</tr>";
	
	echo "<tr>";
	echo "<td class='tab_bg_1' valign='top' colspan='2'>";

	// table commentaires
	echo "<table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td valign='top'>";
	echo $lang["printers"][12].":	</td>";
	echo "<td align='center'><textarea cols='35' rows='4' name='comments' >".$printer->fields["comments"]."</textarea>";
	echo "</td></tr></table>";

	echo "</td>";
	echo "</tr>";
	
		echo "<tr>";

	if ($template) {

			if (empty($ID)||$withtemplate==2){
			echo "<td class='tab_bg_2' align='center' colspan='2'>\n";
			echo "<input type='hidden' name='ID' value=$ID>";
			echo "<input type='submit' name='add' value=\"".$lang["buttons"][8]."\" class='submit'>";
			echo "</td>\n";
			} else {
			echo "<td class='tab_bg_2' align='center' colspan='2'>\n";
			echo "<input type='hidden' name='ID' value=$ID>";
			echo "<input type='submit' name='update' value=\"".$lang["buttons"][7]."\" class='submit'>";
			echo "</td>\n";
			}


	} else {

		echo "<td class='tab_bg_2' valign='top' align='center'>";
		echo "<input type='hidden' name='ID' value=\"$ID\">\n";
		echo "<input type='submit' name='update' value=\"".$lang["buttons"][7]."\" class='submit'>";
		echo "</td></form>\n\n";
		echo "<form action=\"$target\" method='post'>\n";
		echo "<td class='tab_bg_2' valign='top' align='center'>\n";
		echo "<input type='hidden' name='ID' value=\"$ID\">\n";
		echo "<div align='center'>";
		if ($printer->fields["deleted"]=='N')
		echo "<input type='submit' name='delete' value=\"".$lang["buttons"][6]."\" class='submit'>";
		else {
		echo "<input type='submit' name='restore' value=\"".$lang["buttons"][21]."\" class='submit'>";
		
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"".$lang["buttons"][22]."\" class='submit'>";
		}
		echo "</div>";
		echo "</td>";

	}
		echo "</form></tr>";

		echo "</table></div>";

	return true;	
	}
	else {
                echo "<div align='center'><b>".$lang["printers"][17]."</b></div>";
                echo "<hr noshade>";
                searchFormPrinters();
                return false;
        }

}


function updatePrinter($input) {
	// Update a printer in the database

	$printer = new Printer;
	$printer->getFromDB($input["ID"]);

	// set new date and make sure it gets updated
	$updates[0]= "date_mod";
	$printer->fields["date_mod"] = date("Y-m-d H:i:s");
	
	// Pop off the last two attributes, no longer needed
	$null=array_pop($input);
	$null=array_pop($input);
	
	// Get all flags and fill with 0 if unchecked in form
	foreach ($printer->fields as $key => $val) {
		if (eregi("\.*flag\.*",$key)) {
			if (!isset($input[$key])) {
				$input[$key]=0;
			}
		}
	}	

	// Fill the update-array with changes
	$x=1;
	foreach ($input as $key => $val) {
		if ($printer->fields[$key] != $input[$key]) {
			$printer->fields[$key] = $input[$key];
			$updates[$x] = $key;
			$x++;
		}
	}

	$printer->updateInDB($updates);

}

function addPrinter($input) {
	// Add Printer, nasty hack until we get PHP4-array-functions
	$db=new DB;
	$printer = new Printer;
	
	// dump status
	$oldID=$input["ID"];

	$null = array_pop($input);
	$null = array_pop($input);

 	// set new date.
 	$printer->fields["date_mod"] = date("Y-m-d H:i:s");
 	
	// fill array for update
	foreach ($input as $key => $val) {
		if (empty($printer->fields[$key]) || $printer->fields[$key] != $input[$key]) {
			$printer->fields[$key] = $input[$key];
		}
	}

	$printer->addToDB();
	$newID=$printer->getInsertElementID();
	
	// ADD Infocoms
	$ic= new Infocom();
	$ic->getFromDB(PRINTER_TYPE,$oldID);
	$ic->fields["FK_device"]=$newID;
	unset ($ic->fields["ID"]);
	$ic->addToDB();
	
		// ADD Ports
	$query="SELECT ID from glpi_networking_ports WHERE on_device='$oldID' AND device_type='".PRINTER_TYPE."';";
	$result=$db->query($query);
	if ($db->numrows($result)>0){
		
		while ($data=$db->fetch_array($result)){
			$np= new Netport();
			$np->getFromDB($data["ID"]);
			unset($np->fields["ID"]);
			unset($np->fields["ifaddr"]);
			unset($np->fields["ifmac"]);
			unset($np->fields["netpoint"]);
			$np->fields["on_device"]=$newID;
			$np->addToDB();
			}
	}

	// ADD Contract				
	$query="SELECT FK_contract from glpi_contract_device WHERE FK_device='$oldID' AND device_type='".PRINTER_TYPE."';";
	$result=$db->query($query);
	if ($db->numrows($result)>0){
		
		while ($data=$db->fetch_array($result))
			addDeviceContract($data["FK_contract"],PRINTER_TYPE,$newID);
	}


}

function deletePrinter($input,$force=0) {
	// Delete Printer
	
	$printer = new Printer;
	$printer->deleteFromDB($input["ID"],$force);
	
} 	

function restorePrinter($input) {
	// Restore Printer
	
	$ct = new Printer;
	$ct->restoreInDB($input["ID"]);
} 

?>
