<?php
/*
        This code is under Gnu General Public License

        +--------------------------------+
        |   DO NOT MODIFY THIS HEADERS   |
        +--------------------------------+---------------+
        |   Written by TuTToWeB                          |
        |   Email: valeriogiuffrida@hotmail.com          |
        |   Real Name: Valerio Giuffrida - Italy         |
        +------------------------------------------------+


+--------------------------------------------------------------------------------+
        |   Version: 0.1, Relased at 23/08/2006 13:13 (GMT + 1.00)
|

+--------------------------------------------------------------------------------+

        +----------------+
        |   Tested on    |
        +----------------+-----+
        |  APACHE => 2.0.55    |
        |     PHP => 5.1.2     |
        +----------------------+

        +---------------------+
        |  How to report bug  |

+---------------------+-----------------------------------------------------------------+
        |   You can e-mail me using the email addres written above. That
email is also my msn   |
        |   contact, so you can use it for contact me on MSN.
|

+---------------------------------------------------------------------------------------+

        +-----------+
        |  Methods  |

+-----------+------------------------------------------------------------------------------------------------+
        |   - mixed Costructor (string $path_countries_list, string
$flags_dir[, string $ip2parse])                  |
        |     +--> Set the path where the countries's list is placed
|
        |     +--> Set the directory where the flags are placed
|
        |     +--> Setting an IP, you can parse it immidiately
|
        |     <--+ The costruction, if ip is setted, the same array of
parseIP method.                               |
        |   - Array parseIP ($string $ip2parse)
|
|
        |     +--> IP to parse
|
        |     <--+ Array(ISO Code, State's Name, Flag Path)
|
        |   - void readCountries(void)
|

+------------------------------------------------------------------------------------------------------------+

        +------------------+
        |  Special Thanks  |

+------------------+-----------------------------------------------------------------------------------------+
        |  I always thank the HTML FORUM COMMUNITY (http://www.html.it)
for the advice about the regular expressions |
        |  A special thanks at Nomia.it (http://www.nomia.it), because
they provide me the list of countries with    |
        |  the ISO codes.
|
        |  I thanks Ripe.net for its database about IP
|
        |  Finally, i thank Wikipedia for the countries's icons 20px
|

+------------------------------------------------------------------------------------------------------------+
*/

class ip2country
{
  var $countries_list; //Percorso lista degli stati
  var $flags_dir;      //Directory con le bandiere
  var $_COUNTRIES;     //Array con la lista degli stati

  function ip2country($cl,$fd,$ip=false)
  {
    $this->countries_list=$cl;
    $this->flags_dir=$fd;

    $this->readCountries() or die ("Unable to read the countries");

    if ((bool)$ip) return $this->parseIP($ip);
  }

  function parseIP($ip)
  {
    $DATABASE = "whois.ripe.net";
    $info = '' ;
    $sk=fsockopen($DATABASE, 43, $errno, $errstr, 30) or  die ("Unable
to connect to the server");
    fputs ($sk, $ip ."\r\n") or die ("Unable to send data to the
server");
    while (!feof($sk))
    {
      $info.= fgets ($sk, 2048);
    }

    if (preg_match( '/^\x20*country\x20*:\x20*(\w{2})/im',$info,$arr ))
    {
      $found=false;
      for($i=0;$i<count($this->_COUNTRIES);$i++)
      {
        $c=$this->_COUNTRIES[$i];
        if (trim($c[0]) == trim($arr[1])) return $c;
      }
      return array("??","","");
    }
    else array("??","","");

  }

  function readCountries()
  {
    if (file_exists($this->countries_list))
    {
      $handle = file($this->countries_list) or die("Unable to open the
countries's file list");
      foreach($handle as $row)
      {
        list($iso,$name,$flag) = explode(";",$row);
        $this->_COUNTRIES[]=array($iso,$name,$this->flags_dir.$flag);
      }

      return true;
    }
    else return false;
  }
}