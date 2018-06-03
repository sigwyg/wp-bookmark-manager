# wp-bookmark-manager

link-manager.phpの拡張機能。

1. link_idを指定してコピーとして新規作成を行う機能
2. 既存のリンクをCSV出力する機能
3. CSVファイルから複数のリンクを取り込み、あるいは更新する機能
    - データが空白の場合(`""`)は何も更新しません。
    - "BLANK" 文字列を入力したカラムは、空白文字列として更新されます

## 注意事項

- WordPress 3.5からリンク機能はデフォルトでは非表示になりました。
- 旧バージョンからのコアアップデートで、リンク機能を使っていた場合は表示されています。
- 強制的にリンクマネージャー画面を表示したい場合は [Links Manager](https://wordpress.org/plugins/link-manager/) プラグインをインストールします。

作者の環境ではLinks Managerプラグインは必要としていなかったため、特に動作確認は行なっていません。

## 作者コメント

通常では非表示となっているニッチな機能であること、作者はPHPの専門でなく(PHPという言語への)熱意もないこと、必要だから作っただけで問題なく動いているうちは特にメンテする気もないことから、WordPress.orgにて公開するつもりはありません。
ご利用はご自由に。PRも歓迎しています。