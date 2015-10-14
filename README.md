# Installation
Clone this project and give the images writing rights (chmod 777).

Change the value of ``$iptc->predefined_message`` to your secret C&C message.

After that upload ``test.jpg`` to facebook, and change the value of ``$facebook_image`` to your absolute facebook image url.

And now you will get your secret C&C message back:
![Result of script](https://raw.githubusercontent.com/brammittendorff/facebook-and-the-problem-with-iptc/master/result.png)

# Problem
Facebook and the problem with iptc is that you can fill in 2 iptc headers. So you can use facebook as an valid C&C server. I have some more good news the image hosts below have the same problem:
* http://postimage.org/ - all iptc
* https://facebook.com/ - 2 iptc
* https://dumpyourphoto.com/ - all iptc
* http://imgup.net/ - all iptc

The iptc headers i have used:

```
private $iptc_header_array = array(
  '2#005'=>'ObjectName',
  '2#007'=>'EditStatus',
  '2#010'=>'Urgency',
  '2#015'=>'Category',
  '2#020'=>'SupplementalCategory',
  '2#025'=>'Keywords',
  '2#040'=>'SpecialInstruction',
  '2#055'=>'DateCreated',
  '2#080'=>'AuthorByline', // (facebook.com)
  '2#085'=>'AuthorBylineTitle',
  '2#090'=>'City',
  '2#095'=>'ProvinceState',
  '2#101'=>'CountryPrimaryLocationName',
  '2#103'=>'OriginalTransmissionReference',
  '2#105'=>'Headline',
  '2#110'=>'Credit',
  '2#115'=>'Source',
  '2#116'=>'CopyrightNotice', // (facebook.com)
  '2#120'=>'CaptionAbstract',
  '2#122'=>'WriterEditor'
);
```

# Solution
The solution is to strip all tags like exif and iptc. So that you are only left with the needed values and there is no way to insert code or comments into an image.
