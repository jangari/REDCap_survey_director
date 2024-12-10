<?php

namespace INTERSECT\SurveyDirector;

use ExternalModules\AbstractExternalModule;
use REDCap;
use Piping;
use Project;

class SurveyDirector extends \ExternalModules\AbstractExternalModule {

    function redcap_survey_complete($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance)
    {
        // Quit if the module is disabled in the project settings
        if ($this -> getProjectSetting('director_enabled') == 0) return;

        // Set debug mode
        $debug = false;
        if ($this -> getProjectSetting('debug') == 1)
        {
            $debug = true;
            $proj = new Project($project_id);
            $longitudinal = REDCap::isLongitudinal();
            $repeating = $proj -> isRepeatingEvent($event_id) || $proj -> isRepeatingForm($event_id, $instrument);
            $event_name = REDCap::getEventNames(true, true, $event_id);
        }

        // Parse each survey
        $surveys = $this -> getProjectSetting('survey');
        for ($s = 0; $s < count($surveys); $s++){

            $survey_select = $this -> getProjectSetting('survey_select')[$s];
            $event_select = $this -> getProjectSetting('event_select')[$s];
            $directives = $this -> getProjectSetting('directive')[$s];

            // Skip if we're not in this instrument, or if we're not in this event
            if ($instrument != $survey_select || (!is_null($event_select) && $event_id != $event_select)) continue;

            // Skip if this survey is disabled, logging that fact if we're in debug mode
            if ($this -> getProjectSetting('survey_enabled')[$s] == 0) {
                if ($debug) {
                    $logMsg = array();
                    (($longitudinal)) ? $logMsg[] = "Survey: $instrument ($event_name)" : $logMsg[] = "Survey: $instrument";
                    if ($repeating) $logMsg[] = "Instance: $repeat_instance";
                    $logMsg[] = "All directives disabled";
                    REDCap::logEvent(
                        "Survey Director Debug",
                        implode("\n", $logMsg),
                        $sql = NULL,
                        $record = $record,
                        $event = $event_id
                    );
                }
                continue;
            }

            // Parse each directive
            for ($d = 0; $d < count($directives); $d++) {

                if ($debug) $directive_index = ($s + 1) . '.' . ($d + 1);

                // Skip if this directive is disabled, and logging that fact if we're in debug mode
                if ($this -> getProjectSetting('directive_enabled')[$s][$d] == 0) {
                    if ($debug) {
                        $logMsg = array();
                        (($longitudinal)) ? $logMsg[] = "Survey: $instrument ($event_name)" : $logMsg[] = "Survey: $instrument";
                        if ($repeating) $logMsg[] = "Instance: $repeat_instance";
                        $logMsg[] = "Directive $directive_index disabled";
                        REDCap::logEvent(
                            "Survey Director Debug",
                            implode("\n", $logMsg),
                            $sql = NULL,
                            $record = $record,
                            $event = $event_id
                        );
                    }
                    continue;
                }

                $condition = $this -> getProjectSetting('condition')[$s][$d];
                $target = $this -> getProjectSetting('target')[$s][$d];

                // If the condition is met...
                if (is_null($condition) || REDCap::evaluateLogic
                    (
                        $condition,
                        $project_id,
                        $record,
                        $event_id,
                        $repeat_instance,
                        null,
                        $instrument // $current_context_instrument, allows instance smart variables to function normally
                    )
                )
                // ...Perform piping on the target
                {
                    $target = Piping::replaceVariablesInLabel(
                        $target, // $label='',
                        $record, // $record=null,
                        $event_id, // $event_id=null,
                        $repeat_instance, // $instance=1,
                        array(), // $record_data=array(),
                        false, // $replaceWithUnderlineIfMissing=true,
                        $project_id, // $project_id=null,
                        false, // $wrapValueInSpan=true
                        '', // $repeat_instrument=''
                        1, // $recursiveCount=1
                        false, // $simulation=false
                        false, //$applyDeIdExportRights=false
                        $instrument // Ensures we get the right instrument context for piping smart variables
                    );
                    if ($debug) {
                        $logMsg = array();
                        (($longitudinal)) ? $logMsg[] = "Survey: $instrument ($event_name)" : $logMsg[] = "Survey: $instrument";
                        if ($repeating) $logMsg[] = "Instance: $repeat_instance"; 
                        $logMsg[] = "Directive $directive_index";
                        if (!is_null($condition)) $logMsg[] = "Condition: $condition (true)";
                        $logMsg[] = "Target: $target";
                        REDCap::logEvent(
                            "Survey Director Debug",
                            implode("\n", $logMsg),
                            $sql = NULL,
                            $record = $record,
                            $event = $event_id
                        );
                    }
                    header('Location: '.$target);
                    $this->exitAfterHook(); // Go there
                    // If we don't return from the function here, then all other directives will be evaluated, and the last one to be true will take effect.
                    return;
                } elseif ($debug) {
                    $logMsg = array();
                    (($longitudinal)) ? $logMsg[] = "Survey: $instrument ($event_name)" : $logMsg[] = "Survey: $instrument";
                    if ($repeating) $logMsg[] = "Instance: $repeat_instance"; 
                    $logMsg[] = "Directive $directive_index";
                    $logMsg[] = "Condition: $condition (false)";
                    REDCap::logEvent(
                        "Survey Director Debug",
                        implode("\n", $logMsg),
                        $sql = NULL,
                        $record = $record,
                        $event = $event_id);
                }
            }
        }
    }
}
