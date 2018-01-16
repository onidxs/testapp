<?php

$accessToken = 'LINE�̃A�N�Z�X�g�[�N��';

// ��M�������b�Z�[�W���
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);

$event = $receive['events'][0];
$replyToken  = $event['replyToken'];
$messageType = $event['message']['type'];

// �����Ă����̂��ʒu���ȊO�������牞�����Ȃ�
if($messageType != "location") exit;

$lat = $event['message']['latitude'];
$lon = $event['message']['longitude'];

// �����Ă����ʒu�������ɂ���Ȃт�API�ɃA�N�Z�X���ăP���^�b�L�[�̓X�܏����擾����
$uri    = 'https://api.gnavi.co.jp/RestSearchAPI/20150630/';
$accKey = '����Ȃт̃A�N�Z�X�L�[�i�A�J�E���g���s�チ�[���ɂđ����Ă��܂��j';

$url  = $uri . '?format=json&name=�P���^�b�L�[&range=5&keyid=' . $accKey . '&latitude=' . $lat . '&longitude=' . $lon;

$json = file_get_contents($url);
$obj  = json_decode($json);

// �X�܏����擾
$count = 0;
$columns = array();
foreach ($obj->rest as $restaurant) {
  $columns[] = array(
    'thumbnailImageUrl' => $restaurant->image_url->shop_image1,
    'text'    => $restaurant->name,
    'actions' => array(array(
                  'type'  => 'uri',
                  'label' => '�ڍׂ�����',
                  'uri'   => $restaurant->url
                ))
  );
  if (++$count > 5) { // �ő�T�X�܂̏���Ԃ�
    break;
  }
}

// LINE�ŕԐM����
$headers = array('Content-Type: application/json',
                 'Authorization: Bearer ' . $accessToken);

if ($columns) {
  $template = array('type'    => 'carousel',
                    'columns' => $columns);

  $message = array('type'     => 'template',
                   'altText'  => '�P���^�b�L�[�̏��',
                   'template' => $template);
} else {
  $message = array('type' => 'text',
                   'text' => '�����[�N���X�}�X�B�c�O�ł����߂��ɃP���^�b�L�[�͂���܂���B');
}

$body = json_encode(array('replyToken' => $replyToken,
                          'messages'   => array($message)));
$options = array(CURLOPT_URL            => 'https://api.line.me/v2/bot/message/reply',
                 CURLOPT_CUSTOMREQUEST  => 'POST',
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_HTTPHEADER     => $headers,
                 CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl);
curl_close($curl);