PHPからfacebookのチャットAPIを叩くサンプルです。
公式のサンプルがそのままでは動かなかったので公開しました。

facebookのAPI 2.0からチャットAPIが使えなくなるようですので、V1.0のみ対応です。

そろそろV1.0も終わりますので、
公式のサンプルがうまく動かないでムキーッとなった人使ってみてください。

最低限メッセージの送信が試せるレベルです。
自己責任でご利用ください。


注意：
　100％届くとは限りません。facebookの仕様（？）のようです。
　スパム判定されることがあります。
  チャットAPIを叩くにはxmpp_loginのパーミッションが必要です。
　友達同士でないと送れません（たぶん）

使用例

$access_token = "<access token here>";
$user_id = "user id"; // 自分のユーザーID

$message = "サンプルメッセージ";

$from = "from user id"; // 送り主のfacebookID
$to = "send user id";   // 送り先のfacebookID
 
$chat = new FacebookChatApi($access_token, $user_id);
$chat->sendMessage($message, $from, $to);




メモ：
　アクセストークンをすでに取得した状態で使用する想定です。
　なので以下の設定は無くても動くかもしれません。

　  const _APP_ID = "<APP ID is here>"; // App ID
    const _APP_SECRET = "<APP secret is here>"; // App Secret

　結構べた書きです。気が向いたら誰か直してください。

