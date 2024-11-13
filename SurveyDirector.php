<?php

namespace INTERSECT\SurveyDirector;

use ExternalModules\AbstractExternalModule;
use REDCap;
use Piping;

class SurveyDirector extends \ExternalModules\AbstractExternalModule {

    function redcap_survey_complete($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance)
    {
        // Quit if the module is disabled in the project settings
        if ($this -> getProjectSetting('director_enabled') == 0) return;

        // Parse each survey
        $surveys = $this -> getProjectSetting('survey');
        for ($s = 0; $s < count($surveys); $s++){

            $survey_select = $this -> getProjectSetting('survey_select')[$s];
            $event_select = $this -> getProjectSetting('event_select')[$s];
            $directives = $this -> getProjectSetting('directive')[$s];

            // Skip if the survey is disabled, if we're not in this instrument, or if we're not in this event
            if ($this -> getProjectSetting('survey_enabled')[$s] == 0 || $instrument != $survey_select || (!is_null($event_select) && $event_id != $event_select)) continue;

            // Parse each directive
            for ($d = 0; $d < count($directives); $d++) {

                // Skip if this directive is disabled
                if ($this -> getProjectSetting('directive_enabled')[$s][$d] == 0) continue;

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
                // ...go to the target
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
                    header('Location: '.$target);
                    $this->exitAfterHook();
                    // If we don't return from the function here, then all other directives will be evaluated, and the last one to be true will take effect.
                    return;
                }
            }
        }
    }
}
