<?php
error_reporting(-1);
ini_set('display_errors', 'on');

class iptc {

  public $predefined_message = "";
  public $local_file = "";
  public $local_file_contents = "";

  private $iptc_header_array = array(
    '2#005'=>'ObjectName',
    '2#007'=>'EditStatus',
    '2#010'=>'Urgency',
    '2#015'=>'Category',
    '2#020'=>'SupplementalCategory',
    '2#025'=>'Keywords',
    '2#040'=>'SpecialInstruction',
    '2#055'=>'DateCreated',
    '2#080'=>'AuthorByline',
    '2#085'=>'AuthorBylineTitle',
    '2#090'=>'City',
    '2#095'=>'ProvinceState',
    '2#101'=>'CountryPrimaryLocationName',
    '2#103'=>'OriginalTransmissionReference',
    '2#105'=>'Headline',
    '2#110'=>'Credit',
    '2#115'=>'Source',
    '2#116'=>'CopyrightNotice',
    '2#120'=>'CaptionAbstract',
    '2#122'=>'WriterEditor'
  );

  // iptc_make_tag() function by Thies C. Arntzen
  private function iptc_make_tag($rec, $data, $value)
  {
    $length = strlen($value);
    $retval = chr(0x1C) . chr($rec) . chr($data);
    if ($length < 0x8000) {
        $retval .= chr($length >> 8) .  chr($length & 0xFF);
    } else {
        $retval .= chr(0x80) . chr(0x04) . chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
    }
    return $retval . $value;
  }

  private function remove_tags($filename)
  {
    list($width, $height) = getimagesize($filename);
    $image_dest = imagecreatetruecolor($width, $height);
    $image = imagecreatefromjpeg($filename);
    imagecopyresampled($image_dest, $image, 0, 0, 0, 0,  $width, $height, $width, $height);
    imagejpeg($image_dest, $filename);
  }

  public function create_iptc($source, $destination, $data)
  {
    // clear image tags
    $this->remove_tags($source);
    $image = getimagesize($source, $info);
    $iptc_data = "";
    foreach ($this->iptc_header_array as $key => $value) {
        $tag = substr($key, 2);
        $iptc_data .= $this->iptc_make_tag(2, $tag, $value.$key.$data);
    }
    // embed data into image
    $content = iptcembed($iptc_data, $source);
    $fp = fopen($destination, "wb");
    fwrite($fp, $content);
    fclose($fp);
  }

  public function read_iptc($image)
  {
    // download and just read if the image has a base64 encoded string
    echo "<b>Serving image url for results: </b><br />\n" . $image . "<br />\n";
    $this->local_file = "facebook.jpg";
    $this->local_file_contents = file_get_contents($image);
    file_put_contents($this->local_file , $this->local_file_contents);
  }

  public function compare($text, $image)
  {
    preg_match_all("/[a-zA-Z0-9\/+]{20,}/", $this->local_file_contents, $base64);
    if(!empty($base64[0])) {
      foreach($base64[0] as $response) {
        similar_text(strtoupper($response), strtoupper($text), $percentage);
        if(number_format($percentage, 0) > 90) {
          $iptc_tag = str_replace($this->predefined_message, "", $response);
          echo "2#".$iptc_tag." - ".$this->iptc_header_array["2#".$iptc_tag]." - with response: $response<br />\n";
          echo "Text: " . base64_decode(str_replace($iptc_tag, "", $response)) . "<br />\n";
        }
      }
    }
  }

  public function get_contents()
  {
    var_dump(strip_tags($this->local_file_contents));
  }

}

$iptc = new iptc();
$iptc->predefined_message = base64_encode("Lorem ipsum dolor sit amet, consectetur adipiscing elit. In ut nibh lorem. Vivamus neque odio, blandit molestie euismod vitae, feugiat ut erat. In porta enim id sodales commodo.");
$total_message = strlen(base64_decode($iptc->predefined_message));
echo "<b>Hidden message in image ($total_message characters): </b><br />\n" . base64_decode($iptc->predefined_message) . "<br />\n";

// http://postimage.org/ - all iptc - http://s28.postimg.org/clazc1hx7/12087056_931474056947112_5870482655699367021_o.jpg
// https://facebook.com/ - 2 iptc -  https://scontent-ams2-1.xx.fbcdn.net/hphotos-xtp1/t31.0-8/12138350_931569530270898_7157633721123481689_o.jpg
// https://dumpyourphoto.com/ - all iptc - https://static.dyp.im/74tv3aXEXk/26e14cc863fb4e2ff9b2e63afb809f39.jpg
// http://imgup.net/ - all iptc - http://h50.imgup.net/testa9a7.jpg

$facebook_image = "https://scontent-ams2-1.xx.fbcdn.net/hphotos-xat1/t31.0-8/12068469_932209523540232_140332007141850262_o.jpg";
echo "<b>My base64 string: </b><br />\n" . $iptc->predefined_message . "<br />\n";
$iptc->create_iptc("original.jpg", "test.jpg", $iptc->predefined_message);
$iptc->read_iptc($facebook_image);
echo "<b>Iptc compared with results: </b><br />\n";
$iptc->compare($iptc->predefined_message, $facebook_image);
