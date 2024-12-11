# REDCap Survey Director

[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.14136906.svg)](https://doi.org/10.5281/zenodo.14136906)

This module allows project designers to direct participants through surveys controlled by logic. Easily develop a 'flow' between your surveys and then to any external URL by configuring multiple alternative targets upon completion of any survey.

## Installation

### From Module Repository

Install the module from the REDCap module repository and enable in the Control Center, then enable on projects as needed.

### From GitHub

Clone the repository and rename the directory to include a version number, e.g., `survey_director_v1.0.0`, and copy to your modules directory, then enable in Control Center and on projects as needed.

## Usage

This module is configured on the External Module settings dialogue. There you will find options to configure <em>surveys</em> (within specific or across all <em>events</em>), <em>directives</em>, <em>conditions</em> and <em>targets</em>. You may configure directives for any or all surveys. A directive consists of an optional condition and a target. If the respondent submits a survey and that survey is configured with directives, then the first directive's condition is tested. If it is true, the participant is directed to the target. If it is false, the next directive is evaluated, and so on. If none of the directives' conditions are true, the survey termination option specified in the survey's settings will be used as a fallback.

This allows for effective control of survey flow with more precision than the Survey Queue, and the ability to configure multiple targets, compared with REDCap's built-in Survey Auto-Continuation.

As the number of targets that can be configured is unlimited, each controlled by REDCap's familiar conditional logic, the possibilities are endless.

### Examples 

- Direct a respondent to a `[new-instance]` of a repeating survey while the number of instances is less than a desired number
- Direct respondents to different external URLs depending on their responses, or other factors such as a random number
- Enable a supplementary survey based on inclusion criteria, such as a substudy
- Directing respondents out to one of multiple different URL endpoints for different contexts, e.g., `nonconsent` versus `ineligible`
- Use REDCap to write a Choose Your Own Adventure story!

The Target supports piping, with both regular variables, event-specific variables, and smart variables. This means that literal URLs may be built using variables, for example to direct panel participants back to their panel provider to specific endpoints with specific values included as directed by that panel provider.

```
https://panelprovider.com/survey/?complete=true&participant_id=[participant-id]&duration=[survey-duration]&follow-up=[event_2_arm_1][survey-url:welcome]
```

### Debugging

Due to the nature of the `redcap_survey_complete` hook, debugging using the traditional method of outputting information to the JavaScript console is not possible. Instead, debug mode will send information to the REDCap project log. For this reason, it is recommended that you do not enable debug mode in Production projects, so that the project log does not contain this information.

Debugging events will display the survey, event (if longitudinal), instance (if repeating), directive ID, condition and whether it was evaluated as true or false, and fully constructed target URL. If a survey or a directive is not enabled, the debug log will also include that information.

## Todo

It would be nice to have a graphical interface in which this module's settings can be more intuitively configured, since the standard External Module Framework's module configuration dialogue can be confusing with multiple levels of sub-settings.

It would be good to be able to select from the list of public surveys the target, rather than having to construct it using the `[survey-url:instrument]` smart variable. Likewise, perhaps a future improvement would be to allow users to configure customised Survey Termination text as another outcome option. A limitation in the External Module Framework with sub-settings and branching logic for those settings means that this is likely to further clutter the interface.

## Citation

If you use this external module for a project that generates a research output, please cite this software in addition to [citing REDCap](https://projectredcap.org/resources/citations/) itself. You can do so using the APA referencing style as below:

> Wilson, A. (2024). REDCap Survey Director [Computer software]. https://github.com/jangari/REDCap_Survey_Director https://doi.org/10.5281/zenodo.14136906

Or by adding this reference to your BibTeX database:

```bibtex
@software{Wilson_REDCap_Survey_Director_2024,
author = {Wilson, Aidan},
title = {{REDCap Survey Director}},
url = {https://github.com/jangari/REDCap_Survey_Director},
year = {2024},
month = {11},
doi = {10.5281/zenodo.14136906}
}
```

These instructions are also available in [GitHub](https://github.com/jangari/REDCap_Survey_Director) under 'Cite This Repository'.
