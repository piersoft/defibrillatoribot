<?php
/**
 * Telegram Bot example per ricerca luoghi (l'esempio per i defibrillatori) nei dintorni tramite piattaforma openstreetmap
 * @author Matteo Tempestini , rebuild by @piersoft
	Funzionamento
	- invio location
	- risposta dai dati openstreetmap
 */
include("Telegram.php");
include("QueryLocation.php");

class mainloop{
	public $log=LOG_FILE;

 function start($telegram,$update)
	{

		date_default_timezone_set('Europe/Rome');
		$today = date("Y-m-d H:i:s");

		// Instances the class
		$db = new PDO(DB_NAME);

		/* If you need to manually take some parameters
		*  $result = $telegram->getData();
		*  $text = $result["message"] ["text"];
		*  $chat_id = $result["message"] ["chat"]["id"];
		*/
		$inline_query = $update["inline_query"];
		$text = $update["message"] ["text"];
		$chat_id = $update["message"] ["chat"]["id"];
		$user_id=$update["message"]["from"]["id"];
		$location=$update["message"]["location"];

		$this->shell($inline_query,$telegram,$db,$text,$chat_id,$user_id,$location);

	}

	//gestisce l'interfaccia utente
	 function shell($inline_query,$telegram,$db,$text,$chat_id,$user_id,$location)
	{
		date_default_timezone_set('Europe/Rome');
		$today = date("Y-m-d H:i:s");
		if (strpos($inline_query["location"],'.') !== false){
			$trovate=0;
			$res=[];
			$id="";
			$i=0;
			$idx=[];
			$distanza=[];
			$id3="";
				$id1="";
			$inline="";
		$id=$inline_query['id'];
		$lat=$inline_query["location"]['latitude'];
		$lon=$inline_query["location"]['longitude'];
//$lat=40.3561; //debug a Lecce
//$lon=18.1771; //debug a Lecce
		//piersoft= 69668132
//	$id1 = $telegram->InlineQueryResultLocation($id, $lat10,$long10, "Lecce");
//		$id2 = $telegram->InlineQueryResultLocation($id, $lat10,$long10, "Patti");
//		$id3 = $telegram->InlineQueryResultLocation($id, $lat,$lon, "La tua posizione / Your position");
	//	array_push($res,$id3);

//$res= array($id3);

//	$content1=array('chat_id'=>69668132,'text'=>"Trovate:".json_encode($res));
//	$telegram->sendMessage($content1);
//	$content=array('inline_query_id'=>$inline_query['id'],'results' =>json_encode($res));
//	$telegram->answerInlineQuery($content);

	//prelevo dati da OSM sulla base della mia posizione
	$osm_data=give_osm_data($lat,$lon);

	//rispondo inviando i dati di Openstreetmap
	$osm_data_dec = simplexml_load_string($osm_data);
	//per ogni nodo prelevo coordinate e nome
	foreach ($osm_data_dec->node as $osm_element) {
		$c=0;
		$nome="";
		foreach ($osm_element->tag as $key) {

			if ($key['k']=='name')
			{
			//	$nome  =utf8_encode($key['v'])."\n";
				$nome=$key['v'];
		//		$content = array('chat_id' => $chat_id, 'text' =>$nome);
		//		$telegram->sendMessage($content);
			}
		}
		//gestione musei senza il tag nome
		if($nome=="")
		{
				$nome=utf8_encode("DAE non identificato su Openstreetmap");
			//	$content = array('chat_id' => $chat_id, 'text' =>$nome);
			//	$telegram->sendMessage($content);
		}
		$long10=floatval($osm_element['lon']);
		$lat10=floatval($osm_element['lat']);
		$theta = floatval($lon)-floatval($long10);
		$dist =floatval( sin(deg2rad($lat)) * sin(deg2rad($lat10)) +  cos(deg2rad($lat)) * cos(deg2rad($lat10)) * cos(deg2rad($theta)));
		$dist = floatval(acos($dist));
		$dist = floatval(rad2deg($dist));
		$miles = floatval($dist * 60 * 1.1515 * 1.609344);
		$milesmt = floatval($dist * 60 * 1.1515 * 1.609344);
		$distanza[$i]['dista']=$milesmt;
		$nome2=str_replace(" ","_",$nome);
		$data=0.0;
		if ($miles >=1){
			$data =number_format($miles, 2, '.', '')." Km";
		} else $data =number_format(($miles*1000), 0, '.', '')." mt";
		$distanza[$i]['dista']=$milesmt;
		$map="https://www.openstreetmap.org/?mlat=".$osm_element['lat']."&mlon=".$osm_element['lon']."#map=19/".$osm_element['lat']."/".$osm_element['lon'];
		$location =preg_replace('/\s+?(\S+)?$/', '', substr($nome2, 0, 200))."\ndista: ".$data;
	//	$idx[$i] = $telegram->InlineQueryResultLocation($id."/".$i, floatval($osm_element['lat']),floatval($osm_element['lon']), $location);
		$idx[$i] = $telegram->InlineQueryResultArticle($id."/".$i, $location, array('message_text'=>"Clicca il link per visualizzare il DAE su mappa:\nClick link to visualize DAE on map:\n".$map,'disable_web_page_preview'=>true),"http://www.piersoft.it/daebot/daebot.png");
		//array_push($res,$idx[$i]);
		$distanza[$i]['ar']=$idx[$i];
		$i++;
		$c++;
	//	$content_geo = array('chat_id' => $chat_id, 'latitude' =>$osm_element['lat'], 'longitude' =>$osm_element['lon']);
	//	$telegram->sendLocation($content_geo);
	 }
	 sort($distanza);
	//crediti dei dati
	if((bool)$osm_data_dec->node)
	{
		$risposta ="\nMAP: http://www.piersoft.it/daebot/map/index.php?lat=".$lat."&lon=".$lon;
		$id3 = $telegram->InlineQueryResultArticle($id."/290", "Mappa tutti DAE attorno a te \nMap with all AED around you\n", array('message_text'=>$risposta,'disable_web_page_preview'=>true),"http://www.piersoft.it/daebot/mappa.png");
		array_push($res,$id3);
		for ($f=0;$f<$i;$f++){
		//array_push($res,$idx[$i]););

			array_push($res,$distanza[$f]['ar']);
		//	$content = array('chat_id' => 69668132, 'text' => $f,'disable_web_page_preview'=>true);
		//	$telegram->sendMessage($content);
		}

	}else
	{
		$id3 = $telegram->InlineQueryResultLocation($id."/0", $lat,$lon, "Nessun DAE nei 2km da te \nNo DAE near 2km from you");
		$res= array($id3);
	}



	$content=array('inline_query_id'=>$inline_query['id'],'results' =>json_encode($res));
	$telegram->answerInlineQuery($content);

	}
		elseif ($text == "/start") {

			$img = curl_file_create('daebot.png','image/png');
			$contentp = array('chat_id' => $chat_id, 'photo' => $img);
			$telegram->sendPhoto($contentp);
			$reply ="In caso di arresto cardiaco la prima cosa da fare è chiamare il 112 (in Europa) e per ora il 118 in Italia. ogni minuto di mancata assistenza, fa perdere il 10% di possibilità di sopravvivenza. Ecco perchè dopo aver chiamato i soccorsi, è opportuno avere un Defibrillatore e un volontario formato nei paraggi. Ma dove trovare un DAE?";
			$reply .= "\nQuesto robot ti indica i Defibrillatori presenti su openStreetMap attorno alla tua posizione.\nInvia la tua posizione tramite apposita molletta (📎) che trovi in basso a sinistra nella chat o tramite il tasto 'Invia la tua posizione'.\nTutti i dati sono prelevati da Openstreetmap. Data in licenza ODbL (c) OpenStreetMap contributors.\nBot realizzato da @piersoft e @iltempe\nTutorial su inserimento DAE in openstreetmap: https://goo.gl/UCcwkw";

$reply.="\n\nIn case of cardiac arrest, the first thing to do is call 112 (in Europe) and now the 118 in Italy. every minute of no assistance, lose 10% chance of survival. That is why after having called for help, you should have a defibrillator and a volunteer form around. But where to find an AED?";
$reply .="\nThis robot tells you the defibrillators on OpenStreetMap around your location.
Send your location via special clip (📎), located at the bottom left in the chat or by button 'Send location'.
All data taken from OpenStreetMap. Licensed ODbL (c) OpenStreetMap contributors.
\n\nBot made by @piersoft and @iltempe. Tutorial: https://goo.gl/UCcwkw";
				$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$log=$today. ",new chat started," .$chat_id. "\n";
				$this->update_mess($telegram,$chat_id);
				exit;
			}
			//gestione invio posizione
			elseif($location!=null)
			{
				$this->location_manager($db,$telegram,$user_id,$chat_id,$location);
				$log=$today. ",location command sent," .$chat_id. "\n";

			}elseif (strpos($text,'clicca') !== false){

				exit;
			}
			//comando errato
			else{
				 $reply = utf8_encode("Hai selezionato un comando non previsto.
							Invia la tua posizione tramite l'apposita molletta che trovi in basso a sinistra nella chat.
							Tutti i dati sono prelevati da Openstreetmap. Data in licenza ODbL.
							(c) OpenStreetMap contributors http://www.openstreetmap.org/copyright");
							$reply .="Wrong command";
				 $content = array('chat_id' => $chat_id, 'text' => $reply);
				 $telegram->sendMessage($content);
				 $log=$today. ",wrong command sent," .$chat_id. "\n";
			 }
			//aggiorna tastiera
			$this->update_mess($telegram,$chat_id);

			//for debug
			//$this->location_manager($db,$telegram,$user_id,$chat_id,$location);

			//aggiorna log
			file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);

	}


	// Crea la tastiera
	 function update_mess($telegram, $chat_id)
		{
			$option = array(array($telegram->buildKeyboardButton("Invia la tua posizione / send your location", false, true)) //this work
												);
		// Create a permanent custom keyboard
		$keyb = $telegram->buildKeyBoard($option, $onetime=true);
		$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Attiva la localizzazione sul tuo smartphone / Turn on your GPS");
		$telegram->sendMessage($content);
			//$content = array('chat_id' => $chat_id, 'text' => "Invia la tua posizione con la molletta in basso a sinistra (📎 è presente solo nell'app ufficiale non su web.telegram.org) per cercare un defibrillatore nelle vicinanze\nSend your position with the clip at the bottom left (📎) to search for a defibrillator nearby.\nClick /start for Info");

			//$bot_request_message=$telegram->sendMessage($content);
		}

	function location_manager($db,$telegram,$user_id,$chat_id,$location)
		{
			$content = array('chat_id' => $chat_id, 'text' => "Attendere.. / Please wait.. ",'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
				date_default_timezone_set('Europe/Rome');
				$today = date("Y-m-d H:i:s");

				$lon=$location["longitude"];
				$lat=$location["latitude"];

				//for debug Prato coordinates
				//$lon=11.0952;
				//$lat=43.8807;

				//prelevo dati da OSM sulla base della mia posizione
				$osm_data=give_osm_data($lat,$lon);

				//rispondo inviando i dati di Openstreetmap
				$osm_data_dec = simplexml_load_string($osm_data);
				//per ogni nodo prelevo coordinate e nome
				foreach ($osm_data_dec->node as $osm_element) {
					$nome="";
					foreach ($osm_element->tag as $key) {

						if ($key['k']=='name')
						{
						//	$nome  =utf8_encode($key['v'])."\n";
							$nome=$key['v'];
						//	$content = array('chat_id' => $chat_id, 'text' =>$nome);
						//	$telegram->sendMessage($content);
						}
					}
					//gestione musei senza il tag nome
					if($nome=="")
					{
							$nome=utf8_encode("DAE senza nome / DAE without name");
							$content = array('chat_id' => $chat_id, 'text' =>$nome);
							$telegram->sendMessage($content);
					}
					$long10=floatval($osm_element['lon']);
					$lat10=floatval($osm_element['lat']);
					$theta = floatval($lon)-floatval($long10);
					$dist =floatval( sin(deg2rad($lat)) * sin(deg2rad($lat10)) +  cos(deg2rad($lat)) * cos(deg2rad($lat10)) * cos(deg2rad($theta)));
					$dist = floatval(acos($dist));
					$dist = floatval(rad2deg($dist));
					$miles = floatval($dist * 60 * 1.1515 * 1.609344);
					$data=0.0;
					if ($miles >=1){
					  $data =number_format($miles, 2, '.', '')." Km";
					} else $data =number_format(($miles*1000), 0, '.', '')." mt";

					$location="\n---------\n".$nome;
					//."\nMappa:\nhttps://www.openstreetmap.org/?mlat=".$osm_element['lat']."&mlon=".$osm_element['lon']."#map=19/".$osm_element['lat']."/".$osm_element['lon'];
					$location.="\nDista: ".$data;
					$content = array('chat_id' => $chat_id, 'text' =>$location,'disable_web_page_preview'=>true );
					$telegram->sendMessage($content);
					$mappa="https://www.openstreetmap.org/?mlat=".$osm_element['lat']."&mlon=".$osm_element['lon']."#map=19/".$osm_element['lat']."/".$osm_element['lon'];
					$option = array( array( $telegram->buildInlineKeyboardButton("MAPPA", $url=$mappa)));
					$keyb = $telegram->buildInlineKeyBoard($option);
					$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Vai alla");
					$telegram->sendMessage($content);

				//	$content_geo = array('chat_id' => $chat_id, 'latitude' =>$osm_element['lat'], 'longitude' =>$osm_element['lon']);
				//	$telegram->sendLocation($content_geo);
				 }

				//crediti dei dati
				if((bool)$osm_data_dec->node)
				{
					$risposta ="Questi sono i defibrillatori distanti da te massimo 2km e presenti su Openstreetmap";
					$risposta .="\nThese defibrillators are distant from you maximum 2km and on OpenStreetMap";
					$rispostamappa ="http://www.piersoft.it/daebot/map/index.php?lat=".$lat."&lon=".$lon;

					$content = array('chat_id' => $chat_id, 'text' => $risposta,'disable_web_page_preview'=>true);
					$bot_request_message=$telegram->sendMessage($content);
					$option = array( array( $telegram->buildInlineKeyboardButton("MAPPA COMPLETA", $url=$rispostamappa)));
			$keyb = $telegram->buildInlineKeyBoard($option);
			$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "<b>Visualizzali tutti su:</b>",'parse_mode'=>"HTML");
			$telegram->sendMessage($content);
				}else
				{
					$risposta="Non ci sono sono DAE nel raggio di 2km, mi spiace! Se ne conosci uno nelle vicinanze mappalo su www.openstreetmap.org usando il tag emergency=defibrillator e name=nome del luogo";
					$risposta.="\nNo DAE are within 2km, sorry! If you know one nearby, map it on www.openstreetmap.org using the tag = emergency defibrillator and name = name of the place";
					$content = array('chat_id' => $chat_id, 'text' => utf8_encode($risposta),'disable_web_page_preview'=>true);
					$bot_request_message=$telegram->sendMessage($content);
				}


				//memorizzare nel DB
				$obj=json_decode($bot_request_message);
				$id=$obj->result;
				$id=$id->message_id;
				$statement = "INSERT INTO ". DB_TABLE_GEO. " (lat,lng,user,text,bot_request_message) VALUES ('" . $lat . "','" . $lon . "','" . $user_id . "','" . $today . "','". $id ."')";
            	$db->exec($statement);
		}


}

?>
