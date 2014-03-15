<?php
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

define('PATH', '');
define('GITHUB_USER', 'babab');
define('BITBUCKET_USER', GITHUB_USER);

session_start();

require_once 'lib.php';

if (isset($_GET['reload'])) {
    $_SESSION = array();
    header("Location: /" . PATH);
    exit;
}

if (!isset($_SESSION['repositories'])) {
    $code = new CodeRepositories();
    $code->github_user = GITHUB_USER;
    $code->bitbucket_user = BITBUCKET_USER;
    $_SESSION['repositories'] = $code->getData();
}

$data = $_SESSION['repositories'];

/* echo '<pre>'; print_r($data); exit; // DEBUGGING */

$css = array(
    '//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css',
    '//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap-theme.min.css',
    '//fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700',
    '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css',
    'main.css',
);

?><!doctype html>
<html>
  <head>
    <meta charset="UTF-8" />
<?php
    foreach ($css as $url)
        echo '<link rel="stylesheet" href="' . $url . '" />';
?>
    <title>code.babab.nl | open-source projects of Benjamin Althues</title>
  </head>
  <body>
    <div class="jumbotron">
      <div class="container">
        <div class="row">
          <div class="col-md-7">
            <h1>code.babab.nl</h1>
            <p>
              These are my current open-source projects on Github and
              Bitbucket. The data that is presented below is gathered by
              requesting from both Github's and Bitbucket's API, combining
              the projects with matching names to single project containers.
            </p>
          </div>
          <div class="col-md-5">
            <div class="jcont pull-right">
              <p>
                fetched from Github
                <?= durationOrDate($_SESSION['gh_api_time']) ?>
                in <?= $_SESSION['gh_api_duration'] ?> ms
                &nbsp; <i class="fa fa-github"></i>
                <br />
                fetched from Bitbucket
                <?= durationOrDate($_SESSION['bb_api_time']) ?>
                in <?= $_SESSION['bb_api_duration'] ?> ms
                &nbsp; <i class="fa fa-bitbucket"></i>
              </p>
              <p>
                <a class="" href="?reload">
                  <i class="fa fa-refresh"></i> &nbsp;
                  sync
                </a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
<?php
    $i = 0;
    foreach ($data as $project) {
        $bb_url = '';
        $bitbucket = null;
        $gh_url = '';
        $github = null;
        $website = '';

        if (isset($project['bb'])) {
            $description = $project['bb']['description'];
            $bb_url = $project['bb']['links']['html']['href'];
            $bitbucket = true;
            $name = $project['bb']['name'];
        }
        if (isset($project['gh'])) {
            $description = $project['gh']['description'];
            $website = $project['gh']['homepage'];
            $gh_url = $project['gh']['html_url'];
            $github = true;
            $name = $project['gh']['name'];
        }

        if ($i % 2 == 0)
          echo '<div class="row">';

        // still in foreach block
?>
        <div class="col-md-6">
          <h2>
            <?php if(isset($project['gh']) && $project['gh']['fork']): ?>
              <i class="fa fa-code-fork"></i>
            <?php endif; ?>

            <?= $name ?>

            <span class="pull-right">
            <?php
                if ($github)
                    echo ' <i class="fa fa-github-square"></i> ';
                if ($bitbucket)
                    echo ' <i class="fa fa-bitbucket-square"></i> ';
            ?>
            </span>

            <?php if(!empty($project['language'])): ?>
              <span class="language label label-default pull-right"
                    style="background-color: #<?php
                      echo makeHexColor($project['language']);
                    ?>">
            <?php endif; ?>

                <?= $project['language'] ?>
              </span>

            <?php if (!empty($website)): ?>
              <br />
              <small>
                <i class="fa fa-link"></i>
                <a href="<?= $website ?>">
                  <?= $website ?>
                </a>
              </small>
              </span>
            <?php endif; ?>
          </h2>

          <ul class="list-unstyled">

            <li>
              <i class="fa fa-clock-o"></i>
              last commit:
              <?php echo durationOrDate($project['timestamp']) ?>
            </li>

            <?php if ($github): ?>
              <li>
                <i class="fa fa-github"></i>
                <a href="<?= $gh_url ?>">
                  <?= $project['gh']['full_name'] ?>
                </a>

                <i class="fa fa-code"></i>
                <small><?= $project['gh']['git_url'] ?></small>
              </li>
            <?php endif; ?>
            <?php if ($bitbucket): ?>
              <li>
                <i class="fa fa-bitbucket"></i>
                <a href="<?= $bb_url ?>">
                  <?= $project['bb']['full_name'] ?>
                </a>

                <i class="fa fa-code"></i>
                <small>
                  <?= $project['bb']['links']['clone'][1]['href'] ?>
                </small>
              </li>
            <?php endif; ?>

          </ul>

          <p><?= $description ?></p>
        </div>

<?php
        if ($i % 2 != 0)
            echo '</div>';

        $i++;
    } // end of foreach block

    if (count($data) % 2 != 0)
        echo '</div>';
?>

      <hr>
      <footer>
        <p>
          Copyright &copy; 2014 Benjamin Althues
        </p>
        <p>&nbsp;</p>
      </footer>
    </div>
  </body>
</html>
