# backlog2esa
[backlog](https://backlog.com/ja/) の課題を取り出し、 [esa](https://esa.io/) に転記する。

# 使い方(How to)

1. 事前にesaに元になるtemplateを作成する
1. `config.php` の各種変数を設定する
1. `run.php` の `$project_id` を[指定する](https://github.com/soudai/backlog2esa/blob/master/run.php#L22)
1. `php run.php` を実行する

```exsample-template.md
# 日時
{date}

# アジェンダ
- [ ] 書記を決める
- Backlogの振り返り
    - [ ] 今週完了したタスク
    - [ ] 優先度高のタスクの確認

# 今週完了したタスク
{complete_issues}

# 優先度高のタスク
{issues}

# 決定事項

# 共有事項

# 宿題

# その他
- 開催場所
    - hogehoge
- 参加者
    - hoge,fuga,foo
```
