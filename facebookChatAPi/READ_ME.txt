PHP����facebook�̃`���b�gAPI��@���T���v���ł��B
�����̃T���v�������̂܂܂ł͓����Ȃ������̂Ō��J���܂����B

facebook��API 2.0����`���b�gAPI���g���Ȃ��Ȃ�悤�ł��̂ŁAV1.0�̂ݑΉ��ł��B

���낻��V1.0���I���܂��̂ŁA
�����̃T���v�������܂������Ȃ��Ń��L�[�b�ƂȂ����l�g���Ă݂Ă��������B

�Œ�����b�Z�[�W�̑��M�������郌�x���ł��B
���ȐӔC�ł����p���������B


���ӁF
�@100���͂��Ƃ͌���܂���Bfacebook�̎d�l�i�H�j�̂悤�ł��B
�@�X�p�����肳��邱�Ƃ�����܂��B
  �`���b�gAPI��@���ɂ�xmpp_login�̃p�[�~�b�V�������K�v�ł��B
�@�F�B���m�łȂ��Ƒ���܂���i���Ԃ�j

�g�p��

$access_token = "<access token here>";
$user_id = "user id"; // �����̃��[�U�[ID

$message = "�T���v�����b�Z�[�W";

$from = "from user id"; // ������facebookID
$to = "send user id";   // ������facebookID
 
$chat = new FacebookChatApi($access_token, $user_id);
$chat->sendMessage($message, $from, $to);




�����F
�@�A�N�Z�X�g�[�N�������łɎ擾������ԂŎg�p����z��ł��B
�@�Ȃ̂ňȉ��̐ݒ�͖����Ă�������������܂���B

�@  const _APP_ID = "<APP ID is here>"; // App ID
    const _APP_SECRET = "<APP secret is here>"; // App Secret

�@���\�ׂ������ł��B�C����������N�������Ă��������B

