<?php

foreach( file("不带CN不带点要查询的13位专利号文件每行一个号.csv" ) as $e){
    $acode = substr($e,0,13);
    if( strlen($acode)!=13 )continue;
    query_patent($acode);
    sleep(1);
}

function query_patent($acode){
    $fp = stream_socket_client("tcp://s.wanfangdata.com.cn:80");
    if( ! $fp ){
        printf("connect server error\n");
        file_put_contents($acode.".err","err");
        return false;
    }
    list($code,$post) = build_post($acode);
    $header = "POST /SearchService.SearchService/search HTTP/1.1\r\n";
    $header.= "Host: s.wanfangdata.com.cn\r\n";
    $header.= "Connection: keep-alive\r\n";
    $header.= sprintf("Content-Length: %d\r\n",strlen($post));
    $header.= "X-User-Agent: grpc-web-javascript/0.1\r\n";
    $header.= "X-Grpc-Web: 1\r\n";
    $header.= "Content-Type: application/grpc-web+proto\r\n";
    $header.= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36\r\n";
    $header.= "Cookies: 这里放COOKIE=请自己获取\r\n";
    $header.= "Accept: */*\r\n";
    $header.= "Origin: http://s.wanfangdata.com.cn\r\n";
    $header.= sprintf("Referer: http://s.wanfangdata.com.cn/patent?q=%s\r\n",$code);
    $header.= "Accept-Encoding: gzip, deflate\r\n";
    $header.= "Accept-Language: zh-CN,zh;q=0.9\r\n";
    $header.= "Cookie: 这里还可以放COOKIE=请自己获取\r\n";
    $header.= "\r\n";
    $header.= $post;
    stream_socket_sendto($fp,$header);
    $response = stream_socket_recvfrom($fp,1500);
    fclose($fp);
    file_put_contents($acode.".bin",$response);
    printf("done %s\n",$acode);
    return true;
}

function build_post($code){
    $cncode = "CN" . substr($code,0,12) . "." . substr($code,-1,1);
    $hex = "00000000250a210a06". bin2hex("patent") . "1210" . bin2hex($cncode) . "280130144201001001";
    return [ $cncode , hex2bin($hex)];
}

