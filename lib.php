<?php
// vim: set fdm=marker fmr=//++,//+-:
/*
 * Copyright (c) 2014 Benjamin Althues <benjamin@babab.nl>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

//++ Utility functions -------------------------------------------------------

function durationOrDate($timestamp)
{
    $delta = time() - $timestamp;

    if ($delta < 60)
        return "$delta second(s) ago";
    elseif ($delta < (60 * 60)) {
        $m = floor($delta / 60);
        $s = $delta % 60;
        return "$m minute(s) and $s second(s) ago";
    }
    elseif ($delta < (24 * 60 * 60)) {
        $h = floor($delta / 3600);
        $mdiv = $delta % 3600;
        $m = floor($mdiv / 60);
        $s = ceil($mdiv % 60);
        return "$h hour(s), $m minute(s) and $s second(s) ago";
    }
    elseif ($delta < (100 * 24 * 60 * 60)) {
        $d = floor($delta / (24 * 3600));
        return "$d day(s) ago";
    }
    else
        return date("r", $timestamp);
}


function makeHexColor($anything)
{
    return substr(str_replace(range('c', 'z'), '', hash('sha512', $anything)),
                  0, 6);
}

//+---------------------------------------------------------------------------
//++ CodeRepositories class --------------------------------------------------

class CodeRepositories {
    static function sortDim2($array, $subkey) {
        $seq = array();
        $ret = array();

        foreach($array as $k=>$v)
            $seq[$k] = strtolower($v[$subkey]);
        asort($seq);

        foreach($seq as $k=>$v)
            $ret[] = $array[$k];
        return $ret;
    }

    static function loadTime($init_microtime) {
        return floor((microtime(true) - $init_microtime) * 1000);
    }

    public $github_user;
    public $bitbucket_user;

    public function getData()
    {
        $gh = $this->fetchGithubRepos();
        $bb = $this->fetchBitbucketRepos();

        $data = array();
        /*
         * Add 'bb' and 'gh' arrays. Set explicit values for timestamp
         * and language in projects, using the values from Github when
         * both are available.
         */
        foreach ($bb as $proj) {
            $data[$proj['name']]['timestamp'] = strtotime($proj['updated_on']);
            $data[$proj['name']]['language'] =
                    $this->_parseLanguage($proj['language']);
            $data[$proj['name']]['bb'] = $proj;
        }
        foreach ($gh as $proj) {
            $data[$proj['name']]['timestamp'] = strtotime($proj['pushed_at']);
            $data[$proj['name']]['language'] =
                    $this->_parseLanguage($proj['language']);
            $data[$proj['name']]['gh'] = $proj;
        }

        return array_reverse(self::sortDim2($data, 'timestamp'));
    }

    protected function fetchGithubRepos($sort_key='pushed_at',
                                        $reverse_sort=true)
    {
        if (!$this->github_user)
            return false;

        $url = "https://api.github.com/users/$this->github_user/repos";

        $mts = microtime(true);
        $repos = json_decode($this->_fetchData($url), true);

        // Replace language for dotfiles repository
        for ($i = 0; $i < count($repos); $i++)
            if ($repos[$i]['name'] === 'dotfiles')
                $repos[$i]['language'] = '~/. & #!';

        $repos = self::sortDim2($repos, $sort_key);
        if ($reverse_sort)
            $repos = array_reverse($repos);
        $_SESSION['gh_api_duration'] = self::loadTime($mts);
        $_SESSION['gh_api_time'] = time();
        return $repos;
    }

    protected function fetchBitbucketRepos($sort_key='updated_on',
                                           $reverse_sort=true)
    {
        $api_prefix = 'https://bitbucket.org/api/2.0/repositories/';

        if (!$this->bitbucket_user)
            return false;

        $mts = microtime(true);
        $page1 = json_decode(
            $this->_fetchData("$api_prefix$this->bitbucket_user"), true
        );
        $page2 = json_decode(
            $this->_fetchData("$api_prefix$this->bitbucket_user?page=2"), true
        );
        $repos_fetched = array_merge($page1['values'], $page2['values']);

        $repos = array();
        foreach ($repos_fetched as $repo) {
            // Replace language for dotfiles repository
            if ($repo['name'] === 'dotfiles')
                $repo['language'] = '~/. and #!';

            // Only add git repositories, excluding hg repositories
            if ($repo['scm'] === 'git')
                $repos[] = $repo;
        }

        $repos = self::sortDim2($repos, $sort_key);
        if ($reverse_sort)
            $repos = array_reverse($repos);
        $_SESSION['bb_api_duration'] = self::loadTime($mts);
        $_SESSION['bb_api_time'] = time();
        return $repos;
    }

    private function _fetchData($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'code.babab.nl'
        ));
        $ret = curl_exec($curl);
        curl_close($curl);
        return $ret;
    }

    private function _parseLanguage($language)
    {
        $l = strtolower($language);

        if ($l === 'php')
            return 'PHP';
        elseif ($l === 'viml')
            return 'VimL';
        elseif ($l === 'javascript')
            return 'JavaScript';
        else
            return ucfirst($l);
    }
}

//+---------------------------------------------------------------------------