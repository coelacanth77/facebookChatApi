<?php
class FacebookChatApi
{
    const _HOST = "chat.facebook.com";
    const _PORT = "5222";

    const _APP_ID = "<APP ID is here>"; // App ID
    const _APP_SECRET = "<APP secret is here>"; // App Secret

    private $_access_token;
    private $_user_id;

    private $_fp;

    private $_errstr;

    /**
     * set $access_token(need scope "xmpp_login")
     */
    public function FacebookChatApi($access_token, $user_id)
    {
        $this->_access_token = $access_token;
        $this->_user_id = $user_id;
    }

    public function sendMessage($message, $from, $to)
    {
        $this->_fp = $this->_connect();
        if (!$this->_fp)
        {
            return $this->_errstr;
        }

        $res = $this->_init();

        if ($res == false)
        {
            return $this->_errstr;
        }

        $this->_sendMessage($message, $from, $to);

        $this->_close();
    }

    private function _sendMessage($message, $from, $to)
    {
        $xml = "<message to='-${to}@chat.facebook.com' from='-${from}@chat.facebook.com' type='chat'><body>${message}</body></message>";
        $this->_send_xml($xml);
    }

    private function _connect()
    {
        $fp = fsockopen(FacebookChatApi::_HOST, FacebookChatApi::_PORT, $errno, $errstr);
        if (!$fp)
        {
            $this->_errstr = "failed socket open NO = / ". $errorno. " Str = ". $errstr;
            return false;
        }
        else
        {
            return $fp;
        }
    }

    private function _init()
    {
        $STREAM_XML = '<stream:stream '.
            'xmlns:stream="http://etherx.jabber.org/streams" '.
            'version="1.0" xmlns="jabber:client" to="chat.facebook.com" '.
            'xml:lang="en" xmlns:xml="http://www.w3.org/XML/1998/namespace">';
        
        $this->_send_xml($STREAM_XML);

        if (!$this->_find_xmpp('STREAM:STREAM'))
        {
            $this->_errstr = "STREAM_XML failed";
            return false;
        }

        if (!$this->_find_xmpp('MECHANISM', 'X-FACEBOOK-PLATFORM'))
        {
            $this->_errstr = "MECHANISM failed";
            return false;
        }

        $START_TLS = '<starttls xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>';

        $this->_send_xml($START_TLS);

        if (!$this->_find_xmpp('PROCEED', null, $proceed)) 
        {
            $this->_errstr = "PROCEED failed";
            return false;
        }
       
        stream_socket_enable_crypto($this->_fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

        $STREAM_XML = '<stream:stream '.
            'xmlns:stream="http://etherx.jabber.org/streams" '.
            'version="1.0" xmlns="jabber:client" to="chat.facebook.com" '.
            'xml:lang="en" xmlns:xml="http://www.w3.org/XML/1998/namespace">';
        
        $this->_send_xml($STREAM_XML);

        if (!$this->_find_xmpp('STREAM:STREAM'))
        {
            $this->_errstr = "STREAM_XML failed 2";
            return false;
        }

        if (!$this->_find_xmpp('MECHANISM', 'X-FACEBOOK-PLATFORM'))
        {
            $this->_errstr = "MECHANISM failed";
            return false;
        }

        $AUTH_XML = '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="X-FACEBOOK-PLATFORM"></auth>';

        $this->_send_xml($AUTH_XML);

        if (!$this->_find_xmpp('CHALLENGE', null, $challenge))
        {
            $this->_errstr = "CHALLENGE failed";
            return false;
        }

        $challenge = base64_decode($challenge);
        $challenge = urldecode($challenge);
        parse_str($challenge, $challenge_array);

        $resp_array = array(
            'method' => $challenge_array['method'],
            'nonce' => $challenge_array['nonce'],
            'access_token' => $this->_access_token,
            'api_key' => FacebookChatApi::_APP_ID,
            'call_id' => 0,
            'v' => '1.0',
        );

        $response = http_build_query($resp_array);

        $xml = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'. base64_encode($response). '</response>';
        
        $this->_send_xml($xml);
        
        if (!$this->_find_xmpp('SUCCESS'))
        {
            $this->_errstr = "response failed";
            return false;
        }

        $this->_send_xml($STREAM_XML);

        if (!$this->_find_xmpp('STREAM:STREAM'))
        {
            $this->_errstr = "STREAM_XML failed 3";
            return false;
        }

        if (!$this->_find_xmpp('STREAM:FEATURES'))
        {
            $this->_errstr = "STREAM:FEATURES failed";
            return false;
        }

        $RESOURCE_XML = '<iq type="set" id="3">'.
            '<bind xmlns="urn:ietf:params:xml:ns:xmpp-bind">'.
            '<resource>fb_xmpp_script</resource></bind></iq>';

        $this->_send_xml($RESOURCE_XML);
        if (!$this->_find_xmpp('JID'))
        {
            $this->_errstr = "JID failed";
            return false;
        }  

        $SESSION_XML = '<iq type="set" id="4" to="chat.facebook.com">'.
            '<session xmlns="urn:ietf:params:xml:ns:xmpp-session"/></iq>'; 

        $this->_send_xml($SESSION_XML);
        if (!$this->_find_xmpp('SESSION'))
        {
            $this->_errstr = "SESSION failed";
            return false;
        }

        $xml = "<presence />";
        $this->_send_xml($xml);

        return true;
    }

    private function _close()
    {
        $CLOSE_XML = '</stream:stream>';

        $this->_send_xml($CLOSE_XML);
        fclose($this->_fp);
    }

    private function _send_xml($xml)
    {
        fwrite($this->_fp, $xml);
    }

    private function _find_xmpp($tag, $value=null, &$ret=null)
    {
        static $val = null, $index = null;
        do
        {
            if ($val === null && $index === null)
            {
                list($val, $index) = $this->_recv_xml();
                if ($val === null || $index === null)
                {
                    return false;
                }
            }
 
            foreach ($index as $tag_key => $tag_array)
            {
                if ($tag_key === $tag)
                {
                    if ($value === null)
                    {
                        if (isset($val[$tag_array[0]]['value']))
                        {
                            $ret = $val[$tag_array[0]]['value'];
                        }
                        return true;
                    }
                    foreach ($tag_array as $i => $pos)
                    {
                        if ($val[$pos]['tag'] === $tag && isset($val[$pos]['value']) && $val[$pos]['value'] === $value)
                        {
                            $ret = $val[$pos]['value'];
                            return true;
                        }
                    }
                }
            }
            $val = $index = null;
        } while (!feof($this->_fp));

        return false;
    }

    private function _recv_xml($size=4096)
    {
        $xml = fread($this->_fp, $size);
        if (!preg_match('/^</', $xml)) {
            $xml = '<' . $xml;
        }

        if ($xml === "")
        {
            return null;
        }
 
        $xml_parser = xml_parser_create();
        xml_parse_into_struct($xml_parser, $xml, $val, $index);
        xml_parser_free($xml_parser);
     
        return array($val, $index);
    }
}

// ex
/**
$access_token = "<access token here>";
$user_id = "user id";

$message = "サンプルメッセージ";

$from = "from user id";
$to = "send user id";
 
$chat = new FacebookChatApi($access_token, $user_id);
$chat->sendMessage($message, $from, $to);
*/