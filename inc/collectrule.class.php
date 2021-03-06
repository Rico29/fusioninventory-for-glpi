<?php

/**
 * FusionInventory
 *
 * Copyright (C) 2010-2016 by the FusionInventory Development Team.
 *
 * http://www.fusioninventory.org/
 * https://github.com/fusioninventory/fusioninventory-for-glpi
 * http://forge.fusioninventory.org/
 *
 * ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of FusionInventory project.
 *
 * FusionInventory is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * FusionInventory is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with FusionInventory. If not, see <http://www.gnu.org/licenses/>.
 *
 * ------------------------------------------------------------------------
 *
 * This file is used to manage the collect rules.
 * The goal is to fill inventory with collect information.
 *
 * ------------------------------------------------------------------------
 *
 * @package   FusionInventory
 * @author    David Durieux
 * @copyright Copyright (c) 2010-2016 FusionInventory team
 * @license   AGPL License 3.0 or (at your option) any later version
 *            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 * @link      http://www.fusioninventory.org/
 * @link      https://github.com/fusioninventory/fusioninventory-for-glpi
 *
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Rules for collect information.
 * The goal is to fill inventory with collect information.
 */
class PluginFusioninventoryCollectRule extends Rule {

   /**
    * The right name for this class
    *
    * @var string
    */
   static $rightname = "plugin_fusioninventory_rulecollect";

   /**
    * Set these rules can be sorted
    *
    * @var boolean
    */
   public $can_sort=TRUE;

   /**
    * Set these rules not have specific parameters
    *
    * @var boolean
    */
   public $specific_parameters = FALSE;


   /**
    * Get name of this type by language of the user connected
    *
    * @return string name of this type
    */
   function getTitle() {
      return __('Computer information rules', 'fusioninventory');
   }



   /**
    * Make some changes before process review result
    *
    * @param array $output
    * @return array
    */
   function preProcessPreviewResults($output) {
      return $output;
   }



   /**
    * Define maximum number of actions possible in a rule
    *
    * @return integer
    */
   function maxActionsCount() {
      return 8;
   }



   /**
    * Code execution of actions of the rule
    *
    * @param array $output
    * @param array $params
    * @return array
    */
   function executeActions($output, $params) {

      if (count($this->actions)) {
         foreach ($this->actions as $action) {
            switch ($action->fields["action_type"]) {

               case "assign" :
                  $output[$action->fields["field"]] = $action->fields["value"];
                  break;

               case "regex_result" :
                  //Regex result : assign value from the regex
                  $res = "";
                  if (isset($this->regex_results[0])) {
                     $res .= RuleAction::getRegexResultById($action->fields["value"],
                                                            $this->regex_results[0]);
                  } else {
                     $res .= $action->fields["value"];
                  }
                  if ($res != ''
                          && ($action->fields["field"] != 'user'
                              && $action->fields["field"] != 'otherserial'
                              && $action->fields["field"] != 'software'
                              && $action->fields["field"] != 'softwareversion')) {
                     $res = Dropdown::importExternal(
                             getItemTypeForTable(
                                     getTableNameForForeignKeyField(
                                             $action->fields['field'])),
                             $res);
                  }
                  $output[$action->fields["field"]] = $res;
                  break;

               default:
                  //plugins actions
                  $executeaction = clone $this;
                  $output = $executeaction->executePluginsActions($action, $output, $params);
                  break;

            }
         }
      }
      return $output;
   }



   /**
    * Get the criteria available for the rule
    *
    * @return array
    */
   function getCriterias() {

      $criterias = array();

      $criterias['regkey']['field']       = 'name';
      $criterias['regkey']['name']        = __('Registry key', 'fusioninventory');
      $criterias['regkey']['table']       = 'glpi_plugin_fusioninventory_collects_registries';

      $criterias['regvalue']['field']     = 'name';
      $criterias['regvalue']['name']      = __('Registry value', 'fusioninventory');

      $criterias['wmiproperty']['field']  = 'name';
      $criterias['wmiproperty']['name']   = __('WMI property', 'fusioninventory');
      $criterias['wmiproperty']['table']  = 'glpi_plugin_fusioninventory_collects_wmis';

      $criterias['wmivalue']['field']     = 'name';
      $criterias['wmivalue']['name']      = __('WMI value', 'fusioninventory');

      $criterias['filename']['field']     = 'name';
      $criterias['filename']['name']      = __('File name', 'fusioninventory');

      $criterias['filepath']['field']     = 'name';
      $criterias['filepath']['name']      = __('File path', 'fusioninventory');

      $criterias['filesize']['field']     = 'name';
      $criterias['filesize']['name']      = __('File size', 'fusioninventory');

      return $criterias;
   }



   /**
    * Get the actions available for the rule
    *
    * @return array
    */
   function getActions() {

      $actions = array();

      $actions['computertypes_id']['name']  = __('Type');
      $actions['computertypes_id']['type']  = 'dropdown';
      $actions['computertypes_id']['table'] = 'glpi_computertypes';
      $actions['computertypes_id']['force_actions'] = array('assign', 'regex_result');

      $actions['computermodels_id']['name']  = __('Model');
      $actions['computermodels_id']['type']  = 'dropdown';
      $actions['computermodels_id']['table'] = 'glpi_computermodels';
      $actions['computermodels_id']['force_actions'] = array('assign', 'regex_result');

      $actions['operatingsystems_id']['name']  = __('Operating system');
      $actions['operatingsystems_id']['type']  = 'dropdown';
      $actions['operatingsystems_id']['table'] = 'glpi_operatingsystems';
      $actions['operatingsystems_id']['force_actions'] = array('assign', 'regex_result');

      $actions['operatingsystemversions_id']['name']  = _n('Version of the operating system', 'Versions of the operating system', 1);
      $actions['operatingsystemversions_id']['type']  = 'dropdown';
      $actions['operatingsystemversions_id']['table'] = 'glpi_operatingsystemversions';
      $actions['operatingsystemversions_id']['force_actions'] = array('assign', 'regex_result');

      $actions['user']['name']  = __('User');
      $actions['user']['force_actions'] = array('assign', 'regex_result');

      $actions['locations_id']['name']  = __('Location');
      $actions['locations_id']['type']  = 'dropdown';
      $actions['locations_id']['table'] = 'glpi_locations';
      $actions['locations_id']['force_actions'] = array('assign', 'regex_result');

      $actions['states_id']['name']  = __('Status');
      $actions['states_id']['type']  = 'dropdown';
      $actions['states_id']['table'] = 'glpi_states';
      $actions['states_id']['force_actions'] = array('assign', 'regex_result');

      $actions['software']['name']  = __('Software');
      $actions['software']['force_actions'] = array('assign', 'regex_result');

      $actions['softwareversion']['name']  = __('Software version', 'fusioninventory');
      $actions['softwareversion']['force_actions'] = array('assign', 'regex_result');

      $actions['otherserial']['name']  = __('Inventory number');
      $actions['otherserial']['force_actions'] = array('assign', 'regex_result');

      return $actions;
   }
}

?>
