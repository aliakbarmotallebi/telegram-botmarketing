<?php ob_start();?>

<?php
   $time_start = microtime(true);
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('../include/restfull.php');

class shopbot extends drupalRest{
  
  public $Token='449183020:AAG_mILHBXB8ZDOvp8E976ugd5_DrWKgZoM';
  public $ChatId;
  public $TEXT;
  public $URl = "http://botmarketing.ir/bot/";
  public $URLImage="http://botmarketing.ir/sites/default/files";
  public $queryId;
  public $queryText;
  public $UserId;
  public $uid= 41;

  function __construct(){
    $string = json_decode(file_get_contents('php://input'),true);
    $this->StartPartie($string);
  }

  public function StartPartie($result){
   if(isset($result['message']['chat']['id'])){
     $this->ChatId= $result['message']['chat']['id'];
     $this->UserName= $result['message']['chat']['first_name'];
     $this->user();
     $this->message=$result['message']['message_id'];
   }
   if(isset($result['message']['reply_to_message']['text'])){
     $this->Reply= $result['message']['reply_to_message']['text'];
   }
   if(isset($result['message']['message_id'])){
     $this->message=$result['message']['message_id'];
   }
   if(isset($result['message']['text'])){
    $this->TEXT= $result['message']['text'];
  }
  if (isset($this->TEXT)) {
    $this->SwitchMain();
    $this->ShowSharing();
  }
  if (isset($result["callback_query"]["data"])) {
    $this->Inline=@$result["callback_query"]["data"];  
    $this->ChatId= $result['callback_query']['message']['chat']['id'];
    $this->callbackId= $result['callback_query']['id'];
    $this->data=$result["callback_query"]["data"];
    $this->update_id=$result["update_id"];
    $this->message_id=$result["callback_query"]['message']['message_id'];
    $this->InlineMain();
  }
  if(isset($result["inline_query"])){
    $this->queryId = $result["inline_query"]["id"];
    $this->queryText = $result["inline_query"]["query"];
    $this->inline_query();
  } 
  if(isset($this->Reply)){
   if($this->Reply == 'نام و نام خانوادگی را وارد کنید'){
     $this->InsertName();
   }else if($this->Reply == 'آدرس محل سکونت خود را به طور کامل وارد کنید'){
    $this->InsertAddress();
  }else if($this->Reply == 'کد پستی را وارد کنید'){
    $this->InsertPostalCode();
  }else if($this->Reply == 'کد پستی را صحیح وارد کنید'){
    $this->InsertPostalCode();
  }else if($this->Reply == 'شماره تماس را وارد کنید'){
    $this->InsertPhone();
  }else if($this->Reply == 'شماره تماس را صحیح وارد کنید'){
    $this->InsertPhone();
  }else if($this->Reply == 'نام محصول را تایپ کنید'){
    $this->Search();
  }else if($this->Reply == 'نظرات و پیشنهادات خود را ارسال کنید'){
    $this->Comment();
  }
}
}

public function InlineMain(){
 $this->user();
 $inline = explode('_',$this->Inline);
 $key=$inline[0];
 if(isset($inline[1]))
   $value=$inline[1];
 if(isset($inline[2]))
   $thisValue=$inline[2];
 switch ($key) {
   case 'menu':
   switch($value){
     case 'ProductCategories':
     $this->Product();
     break;
     case 'Newproduct':
     $this->NewProduct();
     break;
     case 'Search':
     $this->Rplay('نام محصول را تایپ کنید');
     break;
     case 'TrackOrder':
     $this->TrackingOrder();
     break;
     case 'Contact':
     $this->FixedCategories();
     break;
     case 'ContactUs':
     $this->Rplay('نظرات و پیشنهادات خود را ارسال کنید');
     break;
     case 'Basket':
     $this->ShoppingCart();
     break;
   }
   break;
   case 'Basket':
   $this->ViewOneShoppingCart($value);
   break;
   case 'DeletOne':
   $this->DeletOnePrudact($value,$thisValue);
   break;
   case 'Cat':
   $this->ShowProduct($value);
   break;
   case 'Pages':
   $this->ShowFixedPages($value);
   break;  
   case 'Buy':
   $this->Order($value);
   break; 
   case 'number':
   $this->number($value,$thisValue);
   break;  
   case 'Back':
   $this->Start('لطفا یکی از گزینه های زیرا را انتخاب نمایید');
   break; 
   case 'ShoppingCart':
   $this->ShoppingCart();
   break;   
   case 'Delete':
   $this->DeleteShapingCort();
   break;      
   case 'CompleteProcess':
   $response='اطلاعات شما قبلا در سیستم ثبت شده ، آیا میخواهید از ان استفاده کنید ؟';
   $this->CheckAddress($response);
   break;      
   case 'ViewAddress':
   $this->ViewAddress();
   break;         
   case 'NewAddress':
   $this->Rplay('نام و نام خانوادگی را وارد کنید');
   break;          
   case 'SendBank':
   $this->Status();
   break;
 }
}

  /*
  * SwitchMain
  */
  public function SwitchMain(){
    switch ($this->TEXT) {
      case '/start':
      $this->AddUser();
      $this->Start(' مفتخریم از اینکه میزبان خرید شما هستیم
        لطفا یکی از گزینه های زیر را انتخاب نمایید');
      break;
    }
  }

  private function Status(){
    $DataTask=json_encode(array("mid"=>$this->UserId,"uid"=>$this->uid,"status"=>0));
    $viewcard =$this->RequestToServer($this->URl.'bot_marketing/viewOrder',$DataTask,'post');
    if(isset($viewcard['message'][0]['id'])){
      $dataorder=json_encode(array("orderid"=>$viewcard['message'][0]['id'],"status"=>0));
      $viewOrderProduct =$this->RequestToServer($this->URl.'bot_marketing/viewOrderProduct',$dataorder,'post');
      foreach($viewOrderProduct['message'] as $value){
       $DataTask=json_encode(array("orderid"=>$viewcard['message'][0]['id'],"pid"=>$value['pid'],"status"=>0,"upstatus"=>1));
       $SelectProduct=$this->RequestToServer($this->URl.'bot_marketing/updateOrderProduct',$DataTask,'post');
     }
     $DataTask=json_encode(array("mid"=>$this->UserId,"uid"=>$this->uid,"status"=>1));
     $SelectProduct=$this->RequestToServer($this->URl.'bot_marketing/updatedOrder',$DataTask,'post');
     if($SelectProduct['message'] == '1'){
       $this->SendText('سفارش شما برای ارسال ثبت شد .
         از بخش پیگیری میتوانید از وضعیت ارسال محصول اطلاع پیدا نمایید .
         از اینکه فروشگاه ما را برای خرید محصول انتخاب کردید متشکریم');
       $this->start('لطفا یکی از گزینه های زیرا را انتخاب نمایید');
     }else{
      $this->SendText('انجام نشد');
    }
  }
}


private function FixedCategories(){
  $data=json_encode(array("uid"=>$this->uid,"vid"=>3));
  $Categories = $this->RequestToServer($this->URl.'bot_marketing/privateTaxonomy',$data,'post');
  error_log(print_r($Categories,true));
  if(is_array($Categories['message']) and !empty($Categories['message'])){
    $i=0;
    while ($i<=count($Categories)){
      $ListPart1='';
      for($a=0;$a<2;$a++){
        if($Categories['message'][$i] )
          $ListPart1[]= ['text' => $Categories['message'][$i]['name'],'callback_data'=>'Pages_'.$Categories['message'][$i]['tid']];
        $i++;
      }
      if(!empty($ListPart1))
        $ListPart[]= $ListPart1;
    }
    $keyboard = array('inline_keyboard' => $ListPart);
    $jsonPoets = json_encode($keyboard);
    $text='لطفا یکی از گزینه های زیرا را انتخاب نمایید';
    $this->SendText($text,$jsonPoets);
  }else{
    $this->show_alert($this->callbackId,'خالی است');
  }
}


public function ShowFixedPages($tid){
  $Data=json_encode(array("tid"=>$tid,"uid"=>$this->uid));
  $view = $this->RequestToServer($this->URl.'bot_marketing/taxonomySelectNodes',$Data,'post');
  error_log(print_r($view,true));
  if(!$view[0][0]){
   foreach ($view as $value){
     foreach($value as $key){
       if(!empty($key['field_image_sp']['und'][0]['filename'])){
         $photo = $this->URLImage.'/img/'.urlencode($key['field_image_sp']['und'][0]['filename']);
         $poets = [
         'inline_keyboard'=>[
         [['text'=>"منو",'callback_data'=>'Back']],
         ]
         ];
         $jsonPoets = json_encode($poets);
         $this->SendPhoto($photo,$key['title']."\n".$key['body']['und'][0]['value'],$jsonPoets);
       }else{
        $poets = [
        'inline_keyboard'=>[
        [['text'=>"منو",'callback_data'=>'Back']],
        ]
        ];
        $jsonPoets = json_encode($poets);
        $this->SendText($key['title']."\n".$key['body']['und'][0]['value'],$jsonPoets);
      }
    }
  }
}else{
 $this->show_alert($this->callbackId,'خالی است');
}
}


private function AddUser(){
  $data=json_encode(array("chatid"=>$this->ChatId,"uid"=>$this->uid));
  $view = $this->RequestToServer($this->URl.'bot_marketing/viewInfoUser',$data,'post');
  if($view['message']=="false"){
    $info = $this->RequestToServer($this->URl.'bot_marketing/adduser',$data,'post');
  }
}


public function Start($text){
  $poets = [
  'inline_keyboard'=>[
  [['text'=>"نمایش محصولات",'callback_data'=>'menu_ProductCategories'],['text'=>"جدید ترین کالا ها",'callback_data'=>'menu_Newproduct']],
  [['text'=>"جستجو کالا",'callback_data'=>'menu_Search'],['text'=>"پیگیری سفارش",'callback_data'=>'menu_TrackOrder']],
  [['text'=>"سبد خرید",'callback_data'=>'menu_Basket'],['text'=>"ارتباط با ما",'callback_data'=>'menu_ContactUs']],
  [['text'=>"صفحه ثابت",'callback_data'=>'menu_Contact']]
  ]
  ];
  $jsonPoets = json_encode($poets);
  $this->SendText($text,$jsonPoets);
}


public function Search(){
  $data=json_encode(array("title"=>$this->TEXT,"uid"=>$this->uid));
  $yes = $this->RequestToServer($this->URl.'bot_marketing/searchNodes',$data,'post');
  foreach($yes['message'] as $value){
    if($value['filename']== null){
     $photo= $this->URLImage."img/img.jpg";
   }else{
     $photo = $this->URLImage.'/img/'.$value['filename'];
   } 
   $poets = [
   'inline_keyboard'=>[
   [['text'=>"خرید",'callback_data'=>'Buy_'.$value['nid']],['text'=>'اشتراک در تلگرام','switch_inline_query'=>$value['nid']]],
   [['text'=>"منو",'callback_data'=>'Back']],
   ]
   ];
   $jsonPoets = json_encode($poets);

   $this->SendPhoto($photo,$value['title']."\n".' قیمت:  '.$value['field_price_value']."\n".@$value['body_value'],$jsonPoets);
 }
}

public function NewProduct(){
  $view = $this->RequestToServer($this->URl."node&parameters['uid']=".$this->uid);
      //  if(isset($view)){
  foreach($view as $value){
   $new = round(time() /86400 );
   $old=round($value['created'] /86400);
   $time2 = $new - $old ;
   if($time2 <= 3 ){
    $selectpruduct = $this->RequestToServer($this->URl."node/".$value['nid']);
    $poets = [
    'inline_keyboard'=>[
    [['text'=>"خرید",'callback_data'=>'Buy_'.$value['nid']],['text'=>'اشتراک در تلگرام','switch_inline_query'=>$value['nid']]],
    [['text'=>"منو",'callback_data'=>'Back']],
    ]
    ];
    $jsonPoets = json_encode($poets);
    $photo=$this->URLImage.'/img/'.$selectpruduct['field_image']['und'][0]['filename'];
    $this->SendPhoto($photo,$value['title']."\n".' قیمت:  '.$selectpruduct['field_price']['und'][0]['value']."\n".@$selectpruduct['body']['und'][0]['value'],$jsonPoets);
   }
  }
}


public function Product(){
  $data=json_encode(array("uid"=>$this->uid,"vid"=>2));
  $yes = $this->RequestToServer($this->URl.'bot_marketing/privateTaxonomy',$data,'post');
  $i=0;
  while ($i<=count($yes)){
    $ListPart1='';
    for($a=0;$a<2;$a++){
      if(isset($yes['message'][$i]))
        $ListPart1[]= ['text' => $yes['message'][$i]['name'],'callback_data'=>'Cat_'.$yes['message'][$i]['tid']];
      $i++;
    }
    if(!empty($ListPart1))
      $ListPart[]= $ListPart1;
  }
  $keyboard = array('inline_keyboard' => $ListPart);
  $jsonPoets = json_encode($keyboard);
  $this->SendText('لطفا نوع محصول را انتخاب نمایید',$jsonPoets);
}


private function number($count,$id){
  $data=json_encode(array("mid"=>$this->UserId,"uid"=>$this->uid,"status"=>0));
  $viewcard =$this->RequestToServer($this->URl.'bot_marketing/viewOrder',$data,'post');
  error_log(print_r($viewcard,true));
  $dataorder=json_encode(array("orderid"=>$viewcard['message'][0]['id'],"pid"=>$id,"status"=>0));
  $viewOrderProduct =$this->RequestToServer($this->URl.'bot_marketing/viewOrderProduct',$dataorder,'post');
  if(is_array($viewOrderProduct['message']) and $viewcard['message'][0]['id']){
    $nubmer =  $viewOrderProduct['message'][0]['count'] + $count;
    $DataTask=json_encode(array("orderid"=>$viewcard['message'][0]['id'],"pid"=>$id,"count"=>$nubmer,"status"=>0));
    $SelectProduct=$this->RequestToServer($this->URl.'bot_marketing/updateOrderProduct',$DataTask,'post');
    $this->show_alert($this->callbackId,$nubmer.' عدد سفارش داده شده');
    $this->NumberProduct(true,$id);
  }else if($viewOrderProduct['message'] == "false" and $viewcard['message'][0]['id']){
   $this->show_alert($this->callbackId,$count.' عدد سفارش داده شده');
   $this->NumberProduct(true,$id);
   $DATA=json_encode(array("orderid"=>$viewcard['message'][0]['id'],"pid"=>$id,"count"=>$count,"status"=>0));
   $SelectProduct=$this->RequestToServer($this->URl.'bot_marketing/addOrderProduct',$DATA,'post');  
 }
}


private function Comment(){
 $Data=json_encode(array("chatid"=>$this->ChatId,"uid"=>$this->uid));
 $view = $this->RequestToServer($this->URl.'bot_marketing/viewInfoUser',$Data,'post');
 if(!$view['message']['flname'] ){
  $data=json_encode(array("chatid"=>$this->ChatId,"uid"=>$this->uid,"flname"=>$this->UserName));
  $InsertName = $this->RequestToServer($this->URl.'bot_marketing/updatedInfoUser',$data,'post');
}
$data=json_encode(array("chatid"=>$this->UserId,"uid"=>$this->uid,"text"=>$this->TEXT));
$InsertComment=$this->RequestToServer($this->URl.'bot_marketing/comment',$data,'post');
if($InsertComment['message']= "1"){
  $this->sendText('ارسال شد');
}
}


public function Order($id){
  $data=json_encode(array("mid"=>$this->UserId,"uid"=>$this->uid,"status"=>0));
  $SelectProduct = $this->RequestToServer($this->URl.'bot_marketing/viewOrder',$data,'post');
  if(!$SelectProduct['message']){
    $DATA=json_encode(array("mid"=>$this->UserId,"uid"=>$this->uid,"status"=>0));
    $Select=$this->RequestToServer($this->URl.'bot_marketing/addOrder',$DATA,'post');
    $this->NumberProduct(false,$id);
  }else if($SelectProduct['message'][0]['id']){
    $this->NumberProduct(false,$id);
  }else{
    $this->SendText('انجام نشد');
  }
}


private function NumberProduct($status,$PCode){
  if(!$status){
    $this->show_alert($this->callbackId,'تعداد محصولات را انتخاب کنید');
    $poets = [
    'inline_keyboard'=>[
    [['text'=>"1",'callback_data'=>"number_1_$PCode"] ,['text'=>"2",'callback_data'=>"number_2_$PCode"],['text'=>"3",'callback_data'=>"number_3_$PCode"],['text'=>"4",'callback_data'=>"number_4_$PCode"]
    ,['text'=>"5",'callback_data'=>"number_5_$PCode"]],
    [['text'=>"6",'callback_data'=>"number_6_$PCode"] ,['text'=>"7",'callback_data'=>"number_7_$PCode"],['text'=>"8",'callback_data'=>"number_8_$PCode"],['text'=>"9",'callback_data'=>"number_9_$PCode"]
    ,['text'=>"10",'callback_data'=>"number_10_$PCode"]],
    [['text'=>"منو",'callback_data'=>'Back']]
    ]
    ];
  }else if($status){
   $this->show_alert($this->callbackId,'تعداد محصولات را انتخاب کنید');
   $poets = [
   'inline_keyboard'=>[
   [['text'=>"1",'callback_data'=>"number_1_$PCode"] ,['text'=>"2",'callback_data'=>"number_2_$PCode"],['text'=>"3",'callback_data'=>"number_3_$PCode"],['text'=>"4",'callback_data'=>"number_4_$PCode"]
   ,['text'=>"5",'callback_data'=>"number_5_$PCode"]],
   [['text'=>"6",'callback_data'=>"number_6_$PCode"] ,['text'=>"7",'callback_data'=>"number_7_$PCode"],['text'=>"8",'callback_data'=>"number_8_$PCode"],['text'=>"9",'callback_data'=>"number_9_$PCode"]
   ,['text'=>"10",'callback_data'=>"number_10_$PCode"]],
   [['text'=>"منو",'callback_data'=>'Back'],['text'=>"حذف از سبد خرید",'callback_data'=>'Basket_'.$PCode]]
   ]
   ];
 }
 $jsonPoets = json_encode($poets);
 $this->editMessageReplyMarkup($jsonPoets);
}


private function DeletOnePrudact($id,$what){
 $data=json_encode(array("mid"=>$this->UserId,"uid"=>$this->uid,"status"=>0));
 $idorder =$this->RequestToServer($this->URl.'bot_marketing/viewOrder',$data,'post');
 $dataorder=json_encode(array("orderid"=>$idorder['message'][0]['id'],"pid"=>$id,"status"=>0));
 $viewOrderProduct =$this->RequestToServer($this->URl.'bot_marketing/removeOrderProduct',$dataorder,'post');
 if($viewOrderProduct['message'] == 1){
  if($what == 'ShoppingCart'){
   $this->show_alert($this->callbackId,'حذف شد');
   $this->Start('لطفا یکی از گزینه های زیرا را انتخاب نمایید');
 }
}
}

private function DeleteShapingCort(){
  $data=json_encode(array("mid"=>$this->UserId,"uid"=>$this->uid,"status"=>0));
  $SelectProduct = $this->RequestToServer($this->URl.'bot_marketing/removeOrder',$data,'post');
  if(is_numeric($SelectProduct['message'])){
    $this->show_alert($this->callbackId,'سبد خرید های شما خالی شد');
    $this->Start('لطفا یکی از گزینه های زیرا را انتخاب نمایید');
  }
}


private function TrackingOrder(){
  $text='';
  $text1='';
  $status1=json_encode(array("mid"=>$this->UserId,"uid"=>$this->uid,"status"=>1));
  $selectorderid=$this->RequestToServer($this->URl.'bot_marketing/viewOrder',$status1,'post');
  foreach ($selectorderid['message'] as $value){
   $dataorder=json_encode(array("orderid"=>$value['id'],"status"=>1));
   $viewOrderProduct =$this->RequestToServer($this->URl.'bot_marketing/viewOrderProduct',$dataorder,'post');
   if($viewOrderProduct['message'] != "false"){
    foreach($viewOrderProduct['message'] as $pid){
     $prodact=$this->RequestToServer($this->URl.'node/'.$pid['pid']);
     $text1.=$prodact['title'].' _';
   }
   $text .='شماره سفارش '.$value['id'] ."\n".' محصول ('.substr($text1,0,-1).') : درحال برسی'."\n\n";
   $text1='';
 }
}
foreach ($selectorderid['message'] as $value){
 $dataorder=json_encode(array("orderid"=>$value['id'],"status"=>2));
 $viewOrderProduct2 =$this->RequestToServer($this->URl.'bot_marketing/viewOrderProduct',$dataorder,'post');
 if($viewOrderProduct2['message'] != "false"){
   foreach($viewOrderProduct2['message'] as $pid){
     $prodact2=$this->RequestToServer($this->URl.'node/'.$pid['pid']);
     $text1.=$prodact2['title'].' _';
   }
   $text .='شماره سفارش '.$value['id'] ."\n".' محصول ('.substr($text1,0,-1).') : ارسال شده'."\n\n";
   $text1='';
 }
}
if(!$selectorderid['message']){
  if(!$viewOrderProduct['message'] and !$viewOrderProduct2['message']){
    $this->SendText('سفارشی وجود ندارد');
  }else{
    $this->SendText('سفارشی وجود ندارد'); 
  }
}
$poets = [
'inline_keyboard'=>[
[['text'=>"منو",'callback_data'=>'Back']],
]
];
$jsonPoets = json_encode($poets);
$this->SendText($text,$jsonPoets);
$text='';
}


private function ShoppingCart(){
  $data=json_encode(array("mid"=>$this->UserId,"uid"=>$this->uid,"status"=>0));
  $viewcard =$this->RequestToServer($this->URl.'bot_marketing/viewOrder',$data,'post');
  if(isset($viewcard['message'][0]['id'])){
    $dataorder=json_encode(array("orderid"=>$viewcard['message'][0]['id'],"status"=>0));
    $viewOrderProduct =$this->RequestToServer($this->URl.'bot_marketing/viewOrderProduct',$dataorder,'post');
    if(is_array($viewOrderProduct['message'])){
      $text1='';    
      $Count =0;
      foreach($viewOrderProduct['message'] as $value){
        $prodact=$this->RequestToServer($this->URl.'node/'.$value['pid']);
        $price=$prodact['field_price']['und'][0]['value'] * $value['count'];
        $Count += $price;
        $text1 .=$prodact['title']."\n".'قیمت : '.$prodact['field_price']['und'][0]['value']."\n".'تعداد کالا : '.$value['count']."\n"."قیمت کل : ".$price."\n\n".'-----------------------------------'."\n\n";
      }
      $text='محصولات شما در سبد خرید'."\n\n". $text1."\n".'مبلغ کل : '.$Count."\n".' .';
      $poets = [
      'inline_keyboard'=>[
      [['text'=>"تکمیل فرایند",'callback_data'=>'CompleteProcess'] ,['text'=>"منو",'callback_data'=>'Back']],
      [['text'=>"خالی کردن سبد خرید",'callback_data'=>'Delete']],
      ]
      ];
      $jsonPoets = json_encode($poets);
    }else{
      $text='محصولی وجود ندارد';
    }
    $this->SendText($text,$jsonPoets);
  }else{
    $text='محصولی وجود ندارد';
    $this->SendText($text);
  }
}


public function ViewOneShoppingCart($id){
  $data=json_encode(array("mid"=>$this->UserId,"uid"=>$this->uid,"status"=>0));
  $viewcard =$this->RequestToServer($this->URl.'bot_marketing/viewOrder',$data,'post');
  $dataorder=json_encode(array("orderid"=>$viewcard['message'][0]['id'],"pid"=>$id,"status"=>0));
  $viewOrderProduct =$this->RequestToServer($this->URl.'bot_marketing/viewOrderProduct',$dataorder,'post');
  $prodact=$this->RequestToServer($this->URl.'node/'.$id);
  if(is_array($prodact)){
    $price=$prodact['field_price']['und'][0]['value'] * $viewOrderProduct['message'][0]['count'];
    $text='مشاهده محصول انتخاب شده  :'."\n".$prodact['title']."\n".'قیمت : '.$prodact['field_price']['und'][0]['value']."\n".'تعداد کالا : '. $viewOrderProduct['message'][0]['count']."\n"."قیمت کل : ".$price;
    $poets = [
    'inline_keyboard'=>[
    [['text'=>"سبد خرید",'callback_data'=>'ShoppingCart'] ,['text'=>"منو",'callback_data'=>'Back']],
    [['text'=>"حذف این محصول از سبد خرید",'callback_data'=>'DeletOne_'.$id.'_ShoppingCart']],
    ]
    ];
    $jsonPoets = json_encode($poets);
    $this->SendText($text,$jsonPoets);
  }
}


private function InsertName(){
  $data=json_encode(array("chatid"=>$this->ChatId,"uid"=>$this->uid,"flname"=>$this->TEXT));
  $InsertName = $this->RequestToServer($this->URl.'bot_marketing/updatedInfoUser',$data,'post');
  if($InsertName['message'] == 1){
    $this->Rplay ('آدرس محل سکونت خود را به طور کامل وارد کنید');
  }
}

private function InsertAddress(){
  $data=json_encode(array("chatid"=>$this->ChatId,"uid"=>$this->uid,"address"=>$this->TEXT));
  $InsertName = $this->RequestToServer($this->URl.'bot_marketing/updatedInfoUser',$data,'post');
  if($InsertName['message'] == 1){
    $this->Rplay('کد پستی را وارد کنید');
  }
}

private function InsertPostalCode(){
  $persian_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
  $latin_num = range(0, 9);
  $string =str_replace($persian_num, $latin_num, $this->TEXT);
  if(is_numeric($string)){
    $data=json_encode(array("chatid"=>$this->ChatId,"uid"=>$this->uid,"postalCode"=>$string));
    $InsertName = $this->RequestToServer($this->URl.'bot_marketing/updatedInfoUser',$data,'post');
    if($InsertName['message'] == 1){
      $this->Rplay('شماره تماس را وارد کنید');
    }
  }else{
    $this->Rplay('کد پستی را صحیح وارد کنید');
  }
}

private function InsertPhone(){
  $persian_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
  $latin_num = range(0, 9);
  $string =str_replace($persian_num, $latin_num, $this->TEXT);
  if(is_numeric($string)){
    $data=json_encode(array("chatid"=>$this->ChatId,"uid"=>$this->uid,"phone"=>$string));
    $InsertName = $this->RequestToServer($this->URl.'bot_marketing/updatedInfoUser',$data,'post');
    if($InsertName['message'] == 1){
      $response='آدرس شما ذخیره شده ایا محصول به این آدرس ارسال شود';
      $this->CheckAddress($response);
    }
  }else{
    $this->Rplay('شماره تماس را صحیح وارد کنید');
  }
}


private function ViewAddress(){
 $Data=json_encode(array("chatid"=>$this->ChatId,"uid"=>$this->uid));
 $ViewAddress = $this->RequestToServer($this->URl.'bot_marketing/viewInfoUser',$Data,'post');
 if(is_array($ViewAddress['message'])){
  $text= '_نام : '.$ViewAddress['message']['flname']."\n".'_ آدرس : '.$ViewAddress['message']['address']."\n ".'_ کد پستی : '.$ViewAddress['message']['postalCode']."\n ".'_ شماره تماس : '.$ViewAddress['message']['phone'];      
  $poets = [
  'inline_keyboard'=>[
  [['text'=>"ویرایش آدرس",'callback_data'=>'NewAddress']]
  ]
  ];
  $jsonPoets = json_encode($poets);
  $this->SendText($text,$jsonPoets);
}
}


private function CheckAddress($response){
 $Data=json_encode(array("chatid"=>$this->ChatId,"uid"=>$this->uid));
 $view = $this->RequestToServer($this->URl.'bot_marketing/viewInfoUser',$Data,'post');
 if($view['message']['flname'] and $view['message']['address'] and $view['message']['postalCode']){
   $DataTask=json_encode(array("chatid"=>$this->ChatId,"uid"=>$this->uid));
   $ViewAddress = $this->RequestToServer($this->URl.'bot_marketing/viewInfoUser',$DataTask,'post');
   $text= '_نام : '.$ViewAddress['message']['flname']."\n".'_ آدرس : '.$ViewAddress['message']['address']."\n ".'_ کد پستی : '.$ViewAddress['message']['postalCode']."\n ".'_ شماره تماس : '.$ViewAddress['message']['phone'];      
   $poets = [
   'inline_keyboard'=>[
   [['text'=>"بله",'callback_data'=>'SendBank']],
   [['text'=>"ویرایش آدرس",'callback_data'=>'NewAddress']]
   ]
   ];
   $jsonPoets = json_encode($poets);
   $this->SendText($text."\n".$response,$jsonPoets);
 }else{
   $this->Rplay('نام و نام خانوادگی را وارد کنید');
 }
}



public function ShowProduct($tid){
  $Data=json_encode(array("tid"=>$tid,"uid"=>$this->uid));
  $view = $this->RequestToServer($this->URl.'bot_marketing/taxonomySelectNodes',$Data,'post');
  if(@!$view[0][0]){
   foreach ($view as $value){
     foreach($value as $key){
      
       foreach($key['field_image']['und'] as $image){
         if($key['field_image']== null){
           $photo= $this->URLImage."img/img.jpg";
         }else{
           $photo = $this->URLImage.'/img/'.$image['filename'];
         } 
         $poets = [
         'inline_keyboard'=>[
         [['text'=>"خرید",'callback_data'=>'Buy_'.$key['nid']],['text'=>'اشتراک در تلگرام','switch_inline_query'=>$key['nid']]],
         [['text'=>"منو",'callback_data'=>'Back']],
         ]
         ];
         $jsonPoets = json_encode($poets);

         
       }
       $this->SendPhoto($photo,$key['title']."\n".' قیمت:  '.$key['field_price']['und'][0]['value']."\n".@$key['body']['und'][0]['value'],$jsonPoets);
     }
   }
   
 }else{
   $this->show_alert($this->callbackId,' محصولی وجود ندارد');
 }
}


private function user(){
  $param=json_encode(array('chatid'=>$this->ChatId,"uid"=>$this->uid));
  $SelectUser = $this->RequestToServer($this->URl.'bot_marketing/viewInfoUser',$param,'post');
  $this->UserId= $SelectUser['message']['id'];
}


private function editMessageReplyMarkup($replaye=''){
  $url="https://api.telegram.org/bot".$this->Token."/sendChatAction?chat_id=".urlencode($this->ChatId).
  "&action=".urlencode('typing');
  file_get_contents($url);
  $url="https://api.telegram.org/bot".$this->Token."/editMessageReplyMarkup?chat_id=".urlencode($this->ChatId).
  "&message_id=".urlencode($this->message_id)."&inline_message_id=".urlencode($this->update_id)."&reply_markup=".urlencode($replaye);
  file_get_contents($url);
}


private function SendText($text,$replaye=''){
  $url="https://api.telegram.org/bot".$this->Token."/sendMessage?chat_id=".urlencode($this->ChatId)."&text=".urlencode($text)."&reply_markup=".urlencode($replaye);
  file_get_contents($url);
}


public function ShowSharing(){
  $this->AddUser();    
  $data=explode(' ',$this->TEXT);
  if(isset($data[1]))
    $DataNod = explode('_',$data[1]);
  if(isset($DataNod[0]) == 'NODE'){
    $prodact=$this->RequestToServer($this->URl.'node/'.$DataNod[1]);
    $text =$prodact['title']."\n".'قیمت : '.$prodact['field_price']['und'][0]['value']."\n".@$prodact['body']['und'][0]['value'];
    $image=$this->URLImage.'/img/'.urlencode($prodact['field_image']['und'][0]['filename']);
    $poets = [
    'inline_keyboard'=>[
    [['text'=>"خرید",'callback_data'=>'Buy_'.$DataNod[1]] ,['text'=>'اشتراک در تلگرام','switch_inline_query'=>$DataNod[1]]],
    [['text'=>"منو",'callback_data'=>'Back']],
    ]
    ];
    $jsonPoets = json_encode($poets);
    $this->SendPhoto($image,$text,$jsonPoets);
  }
}


private function inline_query(){
       if($this->queryText !==''){
        if(is_numeric($this->queryText)){
         $view = $this->RequestToServer('http://botmarketing.ir/?q=bot/node/'.$this->queryText);
           if($view){
        $in = array();
                $code = $this->queryText;
                $price= $view['field_price']['und'][0]['value'];
                $image=$this->URLImage.'/img/'.$view['field_image']['und'][0]['filename'];
                $jjjjjjj = array('inline_keyboard'=>array(array(array('text'=>"سفارش میدم!",'url'=>"https://telegram.me/codakmodlbot?start=NODE_".$this->queryText))));
          $entry = array(
              "type" => "article", 
              "id" => $code, 
              "title" => $view['title'], 
              "description" => "قیمت : ".$view['field_price']['und'][0]['value'],
              "reply_markup" => $jjjjjjj,
                        "thumb_url" => $image,
              "input_message_content" => array("message_text" => $view['title']."\n".'قیمت : '.$view['field_price']['und'][0]['value']."\n".@$view['body']['und'][0]['value'])
            );
            $in[] = $entry;
             }
            $post = json_encode($in);
        $this->answerInlineQuery($post);
        }
       }
}

private function answerInlineQuery($results){
  $url="https://api.telegram.org/bot".$this->Token."/answerInlineQuery?inline_query_id=".urlencode($this->queryId).
  "&results=".urlencode($results);
  file_get_contents($url);
}

public function SendPhoto($photo,$text,$replaye=''){
  $url="https://api.telegram.org/bot".$this->Token."/sendChatAction?chat_id=".urlencode($this->ChatId).
  "&action=".urlencode('upload_document');
  file_get_contents($url);
  $url = "https://api.telegram.org/bot".$this->Token."/sendPhoto?chat_id=".$this->ChatId."&photo=".urlencode($photo)."&caption=".urlencode($text)."&reply_markup=".urlencode($replaye);
  file_get_contents($url);

}

private function Rplay($text){
  $reply_markup = json_encode(
    ['force_reply'=>true,'selective'=>true]);
  $this->SendText($text,$reply_markup);
}


function show_alert($id,$text){
  $url="https://api.telegram.org/bot".$this->Token."/answerCallbackQuery?callback_query_id=$id&text=$text&show_alert=true";
  var_dump(file_get_contents($url));
}

}
$time_end = microtime(true);
    $time = $time_end - $time_start;
    echo "Process Time:".round($time*1000);
    error_log("Process Time:".round($time*1000));
$Start= new shopbot();
