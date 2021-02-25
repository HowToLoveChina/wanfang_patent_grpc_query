<?php


$dh = opendir(".");
if( ! $dh ){
    printf("error open dir\n");
    return ;
}
while( $item = readdir($dh)){
    if( $item{0} == '.'  )continue;
    $bin = file_get_contents($item);
    $n = strpos($bin,"\r\n\r\n");
    $bin = substr($bin,$n+4);   #去掉http头
    $n = strpos($bin,"\r\n");
    $bin = substr($bin,$n+2);   #去掉chunk字节数
    #printf("=======================%s====================\n",$item);
    list($unit,$name) = decode_grpc_unit($bin);
    $code = substr($item,0,13);
    printf("%s => %s => %s \n",$code,$unit,$name);
    if($unit == "" )continue;
    $sql = sprintf("UPDATE XXXXX SET 专利名称='%s' , 专利权人='%s' WHERE 专利号='%s'",$name,$unit,$code);
    printf("%s\n",$sql);
}
closedir($dh);

function field($bin,&$start,$size){
    $r = substr($bin,$start,$size);
    $start += $size;
    return $r;
}

function decode_grpc_unit( $bin ){
    $start = 0 ; 
    $length = strlen($bin);
    $magic = field($bin,$start,12);
    $name = "";
    $unit = "";
    while($start<$length){
        $field_type = unpack("C*",field($bin,$start,1))[1];
        if( $field_type == 0xBA ){
            #跳过不能识别的格式
            field($bin,$start,3);
            continue;
        }
        $field_length = unpack("C",field($bin,$start,1))[1];
        $field = field($bin,$start,$field_length);
        if( $field_type == 0x12 ){
            #0x12有多个，最后才是需要的专利名称
            $name = $field;
        }
        if( $field_type == 0x3a ){
            #printf("type %02x , length %d %s\n",$field_type , $field_length,$field);
            $unit = $field;
            return [$field,$name];
        }
        #printf("type %02x , length %d %s\n",$field_type , $field_length,$field);
   }
   return ["",""];
}

