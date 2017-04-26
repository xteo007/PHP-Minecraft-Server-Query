<?php
/**
 * PHP Minecraft Server Query
 *
 * @link        https://github.com/xteo007/PHP-Minecraft-Server-Query/
 * @author      Xteo007 <info@xteo007.pw>
 * @copyright   Copyright (c) 2017 Xteo007
 * @license     https://github.com/xteo007/PHP-Minecraft-Server-Query/blob/master/LICENSE
 */
    class MinecraftServerQuery {

        private $timeout;

        public function __construct($timeout = 2) {
            $this->timeout = $timeout;
        }
		
		// Ricavo il Tag HTML dal Nome
		public function getTypeFont($text) {
			switch ($text) {
				case "obfuscated":
					return "";
					break;
				case "bold":
					return "b";
					break;
				case "strikethrough":
					return "s";
					break;
				case "underline":
					return "u";
					break;
				case "italic":
					return "i";
					break;
				case "reset":
					return "";
					break;
			}			
		}
		
		// Ricavo il Codice del Colore dal Nome.
		public function getColorFont($text,$type='foreground') {
			
			if( $type == 'foreground' ) {
				switch ($text) {
					case "black":
						return "#000000";
						break;
					case "dark_blue":
						return "#0000AA";
						break;
					case "dark_green":
						return "#00AA00";
						break;
					case "dark_aqua":
						return "#00AAAA";
						break;
					case "dark_red":
						return "#AA0000";
						break;
					case "dark_purple":
						return "#AA00AA";
						break;
					case "gold":
						return "#FFAA00";
						break;
					case "gray":
						return "#AAAAAA";
						break;
					case "dark_gray":
						return "#555555";
						break;
					case "blue":
						return "#5555FF";
						break;
					case "green":
						return "#55FF55";
						break;
					case "aqua":
						return "#55FFFF";
						break;
					case "red":
						return "#FF5555";
						break;
					case "light_purple":
						return "#FF55FF";
						break;
					case "yellow":
						return "#FFFF55";
						break;
					case "white":
						return "#FFFFFF";
						break;				
				}
				
				return 'bho';
				
			} else if( $type == 'background' ){
				switch ($text) {
					case "black":
						return "#000000";
						break;
					case "dark_blue":
						return "#00002A";
						break;
					case "dark_green":
						return "#002A00";
						break;
					case "dark_aqua":
						return "#002A2A";
						break;
					case "dark_red":
						return "#2A0000";
						break;
					case "dark_purple":
						return "#2A002A";
						break;
					case "gold":
						return "#2A2A00";
						break;
					case "gray":
						return "#2A2A2A";
						break;
					case "dark_gray":
						return "#151515";
						break;
					case "blue":
						return "#15153F";
						break;
					case "green":
						return "#153F15";
						break;
					case "aqua":
						return "#153F3F";
						break;
					case "red":
						return "#3F1515";
						break;
					case "light_purple":
						return "#3F153F";
						break;
					case "yellow":
						return "#3F3F15";
						break;
					case "white":
						return "#3F3F3F";
						break;				
				}
				
				return 'bho';
				
			}
			
		}

        public function getStatus($host = '127.0.0.1', $port = 25565, $version = '1.7.*') {

            if (substr_count($host , '.') != 4) $host = gethostbyname($host);

            $serverdata = array();
            $serverdata['hostname'] = $host;
            $serverdata['version'] = false;
            $serverdata['protocol'] = false;
            $serverdata['players'] = false;
            $serverdata['maxplayers'] = false;
            $serverdata['motd'] = false;
			$serverdata['motd_html'] = false;
			$serverdata['motd_obj'] = false;
            $serverdata['motd_raw'] = false;
            $serverdata['favicon'] = false;
            $serverdata['ping'] = false;

            $socket = $this->connect($host, $port);

            if(!$socket) {
                return false;
            }

            if(preg_match('/1.7|1.8/',$version)) {

                $start = microtime(true);

                $handshake = pack('cccca*', hexdec(strlen($host)), 0, 0x04, strlen($host), $host).pack('nc', $port, 0x01);

                socket_send($socket, $handshake, strlen($handshake), 0); // Mi Conntetto al Server
                socket_send($socket, "\x01\x00", 2, 0);
                socket_read( $socket, 1 );

                $ping = round((microtime(true)-$start)*1000); // Calcolo il ping del Server MC

                $packetlength = $this->read_packet_length($socket);

                if($packetlength < 10) {
                    return false;
                }

                socket_read($socket, 1);

                $packetlength = $this->read_packet_length($socket);

                $data = socket_read($socket, $packetlength, PHP_NORMAL_READ);

                if(!$data) {
                    return false;
                }

                $data = json_decode($data);

                $serverdata['version'] = $data->version->name;
                $serverdata['protocol'] = $data->version->protocol;
                $serverdata['players'] = $data->players->online;
                $serverdata['maxplayers'] = $data->players->max;

				$descriptionRaw = isset($data->description) ? $data->description : false;
				$description = $descriptionRaw;
				
				// Genero il codice HTML del MOTD del Server MC
				if (gettype($descriptionRaw) == 'object' && isset($descriptionRaw->extra)) {
					$description = '';
					foreach ($descriptionRaw->extra as $item) {
						
						$html_color = false;
						
						$description .= isset($item->bold) && $item->bold ? '<b>' : '';
						$description .= isset($item->strikethrough) && $item->strikethrough ? '<s>' : '';
						$description .= isset($item->underlined) && $item->underlined ? '<u>' : '';
						$description .= isset($item->italic) && $item->italic ? '<i>' : '';
						$description .= '<font color="' . $item->color . '">' . $item->text . '</font>';
						$description .= isset($item->italic) && $item->italic ? '</i>' : '';
						$description .= isset($item->underlined) && $item->underlined ? '</u>' : '';
						$description .= isset($item->strikethrough) && $item->strikethrough ? '</s>' : '';
						$description .= isset($item->bold) && $item->bold ? '</b>' : '';
						
						$description_html .= isset($item->bold) && $item->bold ? '<b>' : '';
						$description_html .= isset($item->strikethrough) && $item->strikethrough ? '<s>' : '';
						$description_html .= isset($item->underlined) && $item->underlined ? '<u>' : '';
						$description_html .= isset($item->italic) && $item->italic ? '<i>' : '';
						$description_html .= '<font color="' . $this->getColorFont($item->color) . '">' . str_replace(' ','&nbsp',$item->text) . '</font>';
						$description_html .= isset($item->italic) && $item->italic ? '</i>' : '';
						$description_html .= isset($item->underlined) && $item->underlined ? '</u>' : '';
						$description_html .= isset($item->strikethrough) && $item->strikethrough ? '</s>' : '';
						$description_html .= isset($item->bold) && $item->bold ? '</b>' : '';
						
					}
				}
				
				$motd = $description;
				$motd_html = $description_html;

                $serverdata['motd'] = $motd;
				$serverdata['motd_raw'] = $data->players->sample[0]->name;
				$serverdata['motd_html'] = $motd_html;
				$serverdata['motd_obj'] = $data->description;
                $serverdata['favicon'] = $data->favicon;
                $serverdata['ping'] = $ping;

            } else {

                $start = microtime(true);

                socket_send($socket, "\xFE\x01", 2, 0);
                $length = socket_recv($socket, $data, 512, 0);

                $ping = round((microtime(true)-$start)*1000);// Calcolo il Ping del Server MC
                
                if($length < 4 || $data[0] != "\xFF") {
                    return false;
                }

                $motd = "";
                $motdraw = "";

                //Analizzo i Dati Ricevuti.
                if (substr((String)$data, 3, 5) == "\x00\xa7\x00\x31\x00"){

                    $result = explode("\x00", mb_convert_encoding(substr((String)$data, 15), 'UTF-8', 'UCS-2'));
                    $motd = $result[1];
                    $motdraw = $motd;

                } else {

                    $result = explode('??', mb_convert_encoding(substr((String)$data, 3), 'UTF-8', 'UCS-2'));
                        foreach ($result as $key => $string) {
                            if($key != sizeof($result)-1 && $key != sizeof($result)-2 && $key != 0) {
                                $motd .= '??'.$string;
                            }
                        }
                        $motdraw = $motd;
                    }

                    $motd = preg_replace("/(??.)/", "", $motd);
                    $motd = preg_replace("/[^[:alnum:][:punct:] ]/", "", $motd); //Remove all special characters from a string

                    $serverdata['version'] = $result[0];
                    $serverdata['players'] = $result[sizeof($result)-2];
                    $serverdata['maxplayers'] = $result[sizeof($result)-1];
                    $serverdata['motd'] = $motd;
                    $serverdata['motd_raw'] = $motdraw;
                    $serverdata['ping'] = $ping;

            }

            $this->disconnect($socket);

            return $serverdata;

        }

        private function connect($host, $port) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if (!@socket_connect($socket, $host, $port)) {
        		$this->disconnect($socket);
        		return false;
    	    }
            return $socket;
        }

        private function disconnect($socket) {
            if($socket != null) {
                socket_close($socket);
            }
        }

        private function read_packet_length($socket) {
            $a = 0;
            $b = 0;
            while(true) {
                $c = socket_read($socket, 1);
                if(!$c) {
                    return 0;
                }
                $c = Ord($c);
                $a |= ($c & 0x7F) << $b++ * 7;
                if( $b > 5 ) {
                    return false;
                }
                if(($c & 0x80) != 128) {
                    break;
                }
            }
            return $a;
        }

    }
