<?php

/*
* Copyright (C) 2015 xiuno.com
*/

function form_radio_yes_no($name, $checked = 0) {
	$checked = intval($checked);
	return form_radio($name, array(1=>lang('yes'), 0=>lang('no')), $checked);
}

function form_radio($name, $arr, $checked = 0) {
	empty($arr) && $arr = array(lang('no'), lang('yes'));
	$s = '';

	foreach((array)$arr as $k=>$v) {
		$add = $k == $checked ? ' checked="checked"' : '';
		$k = htmlspecialchars($k, ENT_QUOTES, 'UTF-8', FALSE); // double_encode=false 避免二次转义
		$v = htmlspecialchars($v, ENT_QUOTES, 'UTF-8', FALSE);
		$s .= "<label class=\"custom-input custom-radio\"><input type=\"radio\" name=\"$name\" value=\"$k\"$add /> $v</label> &nbsp; \r\n";
	}
	return $s;
}

function form_checkbox($name, $checked = 0, $txt = '', $val = 1) {
	$add = $checked ? ' checked="checked"' : '';
	$txt = htmlspecialchars($txt, ENT_QUOTES, 'UTF-8', FALSE);
	$val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8', FALSE);
	$s = "<label class=\"custom-input custom-checkbox mr-4\"><input type=\"checkbox\" name=\"$name\" value=\"$val\" $add /> $txt</label>";
	return $s;
}

/*
	form_multi_checkbox('cateid[]', array('value1'=>'text1', 'value2'=>'text2', 'value3'=>'text3'), array('value1', 'value2'));
*/
function form_multi_checkbox($name, $arr, $checked = array()) {
	$s = '';
	foreach($arr as $value=>$text) {
		$ischecked = in_array($value, $checked);
		$s .= form_checkbox($name, $ischecked, $text, $value);
	}
	return $s;
}

function form_select($name, $arr, $checked = 0, $id = TRUE) {
	if(empty($arr)) return '';
	$idadd = $id === TRUE ? "id=\"$name\"" : ($id ? "id=\"$id\"" : '');
	$s = "<select name=\"$name\" class=\"custom-select\" $idadd> \r\n";
	$s .= form_options($arr, $checked);
	$s .= "</select> \r\n";
	return $s;
}

function form_options($arr, $checked = 0) {
	$s = '';
	foreach((array)$arr as $k=>$v) {
		$add = $k == $checked ? ' selected="selected"' : '';
		$k = htmlspecialchars($k, ENT_QUOTES, 'UTF-8', FALSE);
		$v = htmlspecialchars($v, ENT_QUOTES, 'UTF-8', FALSE);
		$s .= "<option value=\"$k\"$add>$v</option> \r\n";
	}
	return $s;
}

function form_text($name, $value, $width = FALSE, $holdplacer = '') {
	$style = '';
	if($width !== FALSE) {
		is_numeric($width) AND $width .= 'px';
		$style = " style=\"width: $width\"";
	}
	$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', FALSE);
	$s = "<input type=\"text\" name=\"$name\" id=\"$name\" placeholder=\"$holdplacer\" value=\"$value\" class=\"form-control\"$style />";
	return $s;
}

function form_hidden($name, $value) {
	$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', FALSE);
	$s = "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"$value\" />";
	return $s;
}

function form_textarea($name, $value, $width = FALSE,  $height = FALSE) {
	$style = '';
	if($width !== FALSE) {
		is_numeric($width) AND $width .= 'px';
		is_numeric($height) AND $height .= 'px';
		$style = " style=\"width: $width; height: $height; \"";
	}
	$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', FALSE);
	$s = "<textarea name=\"$name\" id=\"$name\" class=\"form-control\" $style>$value</textarea>";
	return $s;
}

function form_password($name, $value, $width = FALSE) {
	$style = '';
	if($width !== FALSE) {
		is_numeric($width) AND $width .= 'px';
		$style = " style=\"width: $width\"";
	}
	$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', FALSE);
	$s = "<input type=\"password\" name=\"$name\" id=\"$name\" class=\"form-control\" value=\"$value\" $style />";
	return $s;
}

function form_time($name, $value, $width = FALSE) {
	$style = '';
	if($width !== FALSE) {
		is_numeric($width) AND $width .= 'px';
		$style = " style=\"width: $width\"";
	}
	$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', FALSE);
	$s = "<input type=\"text\" name=\"$name\" id=\"$name\" class=\"form-control\" value=\"$value\" $style />";
	return $s;
}



/**用法

echo form_radio_yes_no('radio1', 0);
echo form_checkbox('aaa', array('无', '有'), 0);

echo form_radio_yes_no('aaa', 0);
echo form_radio('aaa', array('无', '有'), 0);
echo form_radio('aaa', array('a'=>'aaa', 'b'=>'bbb', 'c'=>'ccc', ), 'b');

echo form_select('aaa', array('a'=>'aaa', 'b'=>'bbb', 'c'=>'ccc', ), 'a');

*/

?>