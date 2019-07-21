<?php
require __DIR__ . "/vendor/autoload.php";

use Itigoppo\BacklogApi\Backlog\Backlog;
use Itigoppo\BacklogApi\Connector\ApiKeyConnector;
use Carbon\Carbon;

/**
 * Class nulab_backlog
 *
 * @property  Backlog backlog
 */
class nulab_backlog
{
    private $space_id;
    private $api_key;
    private $backlog;
    private $markdown_links = [];
    /**
     * @var string com or jp
     */
    private $backlog_domain = 'com';

    /**
     * nulab_backlog constructor.
     *
     * @param string $space_id
     * @param string $api_key
     */
    public function __construct(string $space_id, string $api_key)
    {
        $this->space_id = $space_id;
        $this->api_key = $api_key;
        $this->backlog = new Backlog(new ApiKeyConnector($this->space_id, $this->api_key, $this->backlog_domain));
    }

    /**
     * backlogの指定されたprojectのタスクを取得して、markdownに置き換える
     *
     * @param int $project_id
     */
    public function make_list(int $project_id)
    {
        // 完了済みの課題の取得
        $complete_query_options = $this->get_complete_query_options($project_id);
        $complete_backlog_issues = $this->get_project_issues($complete_query_options);
        $this->markdown_links['complete_issues'] = $this->get_markdown_link($complete_backlog_issues);

        // 優先度高の課題の取得
        $task_query_options = $this->get_task_query_options($project_id);
        $task_backlog_issues = $this->get_project_issues($task_query_options);
        $this->markdown_links['issues'] = $this->get_markdown_link($task_backlog_issues);
    }

    /**
     * make_listによって作成されたmarkdownのlistを返す
     *
     * @return array markdown_links
     */
    public function get_markdown_links()
    {
        return $this->markdown_links;
    }

    /**
     * 有効なプロジェクトの一覧を取得
     *
     * https://developer.nulab.com/ja/docs/backlog/api/2/get-project-list/
     * @return array backlog projects
     */
    public function get_projects(): array
    {
        $query_params = [
            'archived' => false
        ];

        return $this->backlog->projects->load($query_params);
    }

    /**
     * 完了済みの課題を取り出すためのquery_optionsを取得
     *
     * @param int $project_id
     * @return array $query_options
     */
    private function get_complete_query_options(int $project_id): array
    {
        $dt = new Carbon('last monday', 'Asia/Tokyo');

        // 先週の月曜日から今週の月曜日までの完了日の範囲を指定する
        $last_monday = $dt->toDateString();
        $next_monday = $dt->addWeek(1)->toDateString();

        $query_options = [
            'projectId[]' => $project_id,
            'count' => 100, //APIの仕様上、最大取得件数は100
            'statusId[0]' => 4, // 完了
            'statusId[1]' => 3, // 処理済み
            'dueDateSince' => $last_monday,
            'dueDateUntil' => $next_monday,
            'sort' => 'dueDate',
        ];

        return $query_options;
    }

    /**
     * 優先度高の課題を取り出すためのquery_optionsを取得
     *
     * @param int $project_id
     * @return array $query_options
     */
    private function get_task_query_options(int $project_id): array
    {
        $query_options = [
            'projectId[]' => $project_id,
            'count' => 100, //APIの仕様上、最大取得件数は100
            'statusId[0]' => 1, // 未対応
            'statusId[1]' => 2, // 処理中
            'priorityId[]' => 2,
            'sort' => 'created',

        ];

        return $query_options;
    }


    /**
     * $query_optionsを元に課題一覧を取得
     *
     * https://developer.nulab.com/ja/docs/backlog/api/2/get-issue-list/
     *
     * @param array $query_options 検索条件
     * @return array backlog issues 課題一覧
     */

    private function get_project_issues(array $query_options): array
    {
        return $this->backlog->issues->load($query_options);
    }

    /**
     * backlogの課題を元にmarkdownに変換して返す
     *
     * @param array $backlog_issues 課題一覧
     * @return array $markdown_links
     */
    private function get_markdown_link(array $backlog_issues): array
    {
        $base_url = 'https://' . $this->space_id . '.backlog.' . $this->backlog_domain . '/view/';
        $markdown_links = [];

        foreach ($backlog_issues as $issue) {
            $url = $base_url . $issue->issueKey;
            $markdown_links[] = "- [$issue->issueKey {$issue->summary}]({$url}) \n  - **{$issue->status->name}** \n  - {$issue->assignee->name}";
        }

        return $markdown_links;
    }
}



