<?php
require(__DIR__ . '/nulab_backlog.php');
require(__DIR__ . '/esa.php');
require(__DIR__ . '/config.php');

$nulab = new nulab_backlog($space_id, $api_key);
$projects = $nulab->get_projects();

foreach ($projects as $project) {
    echo $project->projectKey . ' => ' . $project->id . PHP_EOL;
}

/**
 * exsample
 * BIZ => xxxx
 * API => xxxx
 * DEV => xxxx
 *
 * Select the project id you want to use
 * 使いたい project id を確認して $project_id に代入します
 */
$project_id = 'your project id';

$nulab->make_list($project_id);
$markdown_links = $nulab->get_markdown_links();

$esa = new esa($access_token, $current_team);
$post = $esa->post($template_post_id);

$esa->make_post_query($post, $markdown_links);
$esa->update($post['number']);