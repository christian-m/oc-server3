#!/usr/local/bin/php -q
<?php
/****************************************************************************
 * For license information see LICENSE.md
 *
 *
 * refresh the search-index of all modified descriptions
 *
 * This is run as separate cronjob because it takes much time, may not
 * fit into the runcron frequency and should run at some time in the night.
 ****************************************************************************/

// needs absolute rootpath because called as cronjob
use Oc\Util\ProcessSync;

$opt['rootpath'] = __DIR__ . '/../../';

require __DIR__ . '/../../lib2/cli.inc.php';
require __DIR__ . '/../../lib2/search/ftsearch.inc.php';

if (!Cronjobs::enabled()) {
    exit;
}

$processSync = new ProcessSync('fill_searchindex');
if ($processSync->enter()) {
    ftsearch_refresh();
    $processSync->leave();
}
