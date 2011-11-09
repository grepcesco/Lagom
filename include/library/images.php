<?php

/**
 * @class Images
 *
 * @brief Classe per la gestione dell'accesso alla base di dati.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * della base di dati.
 *
 */

class Images {
	const UPLOAD_DIRECTORY = "data/upload/";
	const MEDIUM_THUMBS_DIRECTORY = "medium_thumbs/";
	const SMALL_THUMBS_DIRECTORY = "small_thumbs/";
	const MEDIUM_AVATAR_DIRECTORY = "avatars/medium_avatars/";
	const SMALL_AVATAR_DIRECTORY = "avatars/small_avatars/";
	/* $allowed_types = array("image/gif","image/x-png","image/pjpeg","image/jpeg");
	if(!in_array($_FILES["upfile"]["type"],$allowed_types)) {
		die("Il file non è di un tipo consentito, sono ammessi solo i seguenti: " . implode(",", $allowed_types) . ".");
	}*/
	public static function insert_image($tmp_name, $file_name, $file_size, $file_type) {
		list($width, $height, $source_image_type, $attr) = getimagesize($tmp_name);

		if($width > $height) { $swidth = 135; $mwidth = 235; $sheight = 0; $mheight = 0; }
		else { $swidth = 0; $mwidth = 0; $sheight = 135; $mheight = 235; }

		// todo: gestione errori!! andrebbe fatta meglio in generale..
		// todo: inserire controllo dimensione file e tipo file
		if(is_uploaded_file($tmp_name)) {
			return self::resize_image($tmp_name, self::make_small_thumb_address($file_name), $swidth, $sheight)
				&& self::resize_image($tmp_name, self::make_medium_thumb_address($file_name), $mwidth, $mheight);
		}
	}

	/* @todo: to refactor some day
	 *
	 */

	public static function insert_avatar($tmp_name, $file_name, $file_size, $file_type) {
		list($width, $height, $source_image_type, $attr) = getimagesize($tmp_name);

		if($width > $height) { $swidth = 56; $mwidth = 235; $sheight = 56; $mheight = 0; }
		else { $swidth = 56; $mwidth = 0; $sheight = 56; $mheight = 235; }

		// todo: gestione errori!! andrebbe fatta meglio in generale..
		// todo: inserire controllo dimensione file e tipo file
		if(is_uploaded_file($tmp_name)) {
			return self::resize_image($tmp_name, self::make_small_avatar_address($file_name), $swidth, $sheight)
				&& self::resize_image($tmp_name, self::make_medium_avatar_address($file_name), $mwidth, $mheight);
		}
	}

	public static function get_upload_directory() {
		return self::UPLOAD_DIRECTORY;
	}

	public static function make_small_thumb_address($file_name) {
		return self::UPLOAD_DIRECTORY 
				. self::SMALL_THUMBS_DIRECTORY
				. "small_" . $file_name;
	}
	
	public static function make_medium_thumb_address($file_name) {
			return self::UPLOAD_DIRECTORY 
				. self::MEDIUM_THUMBS_DIRECTORY 
				. "medium_" . $file_name;
	}

	public static function make_small_avatar_address($file_name) {
		return self::UPLOAD_DIRECTORY 
				. self::SMALL_AVATAR_DIRECTORY
				. "small_" . $file_name;
	}
	
	public static function make_medium_avatar_address($file_name) {
			return self::UPLOAD_DIRECTORY 
				. self::MEDIUM_AVATAR_DIRECTORY 
				. "medium_" . $file_name;
	}
	public static function delete_image($path_to_image) {
		if(file_exists($path_to_image)) {
			return unlink($path_to_image);
		} else { return false; }
	}

	/**
	 * Ridimensiona una immagine.
	 *   
	 * @param $source_image_address
	 * @param $dest_image_address
	 * @param $dest_width
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * 
	 */
	public static function resize_image($source_image_address, $dest_image_address, $dest_width, $dest_height) {
		// Ottengo le informazioni sull'immagine originale
		list($source_width, $source_height, $source_image_type, $attr) = getimagesize($source_image_address);

		switch ($source_image_type) {
			case IMAGETYPE_GIF:
				$source = imagecreatefromgif($source_image_address);
				break;
		
			case IMAGETYPE_JPEG:
				$source = imagecreatefromjpeg($source_image_address);
				break;

			case IMAGETYPE_PNG:
				$source = imagecreatefrompng($source_image_address);
				break;
		}

		// If the source_width or source_height is give as 0, find the correct ratio using the other value
		if(!$dest_height and $dest_width) 
			// Get the new source_height in the correct ratio
			$dest_height = $source_height * $dest_width / $source_width; 
		if($dest_height and !$dest_width) 
			// Get the new source_width in the correct ratio
			$dest_width	= $source_width  * $dest_height / $source_height;

		$thumb = imagecreatetruecolor($dest_width, $dest_height);
		imagecopyresized($thumb, $source, 0, 0, 0, 0, $dest_width, $dest_height, $source_width, $source_height);

		// Salvo l'immagine ridimensionata
		return imagejpeg($thumb, $dest_image_address, 75);
	}
}
