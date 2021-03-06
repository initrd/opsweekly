<?php

/**
 *  A 'weekly' provider, or 'hints' is designed to prompt the
 *  user to remember what they did in the last week, so they can
 *  fill out their weekly report more accurately.
 *
 *  The class name doesn't matter.. It's picked in the config.
 *
 *  Your constructor should accept the following variables:
 *  - $username: The username of the person the hints are for
 *  - $config: An array of the config options that came from config.php
 *  - $events_from: The beginning of the period to show hints for
 *  - $events_to: The end of the period to show hints for
 *
 *  Then, just create a public function 'printHints' that returns HTML to be
 *  inserted into the sidebar of the "add report" page.
 *
 **/

class GithubHints {
    private $github_url;
    private $events_from, $events_to;
    private $username;

    public function __construct($username, $config, $events_from, $events_to) {
        $this->github_url = $config['github_url'];
        $this->username = $username;
        $this->events_from = $events_from;
        $this->events_to = $events_to;
    }


    public function printHints() {
        if(!$activities = $this->getGithubActivity()) {
            return insertNotify("error", "No Github activity could be loaded");
        }

        if (count($activities) > 0) {
            $html = "<ul>";
            foreach ($activities as $activity) {
                # There are other activity types other than commit, but for now we'll pretend they don't exist.
                if (isset($activity->repository->owner)) {
                    $friendly_name = "{$activity->repository->owner}/{$activity->repository->name}";
                    $url_base = "{$activity->repository->url}/commit/";
                    if (isset($activity->payload->shas)) {
                        foreach ($activity->payload->shas as $commit) {
                            $html .= '<li><a href="' . $url_base . $commit[0] . '" target="_blank">';
                            $html .= "{$friendly_name}</a> - {$commit[2]}</li>";
                        }
                    }
                }
            }
            $html .= "</ul>";
            return $html;

        } else {
            return insertNotify("error", "No Github activity could be found and/or loaded");
        }

    }

    private function getGithubActivity() {
        if (false == ($json = @file_get_contents("{$this->github_url}/{$this->username}.json"))) {
            return false;
        }

        if (false == ($gh_activity = json_decode($json))) {
            return false;
        } else {
            return $gh_activity;
        }
    }

}
?>

