<?php

//�Z�b�V�����̐錾
session_start();

$slide_speed = $_POST['slide_speed'];
$secret_status = $_POST['secret_status'];
$now_password = $_POST['now_password'];
$new_password = $_POST['new_password'];
$pass_change_flug = $_POST['pass_change_flug'];

//$secret_status��true,false�Œl��������Ă���̂�visible,hidden�ɕς���
IF($secret_status == "true"){
	$secret_status = "visible";
}elseif($secret_status == "false"){
	$secret_status = "hidden";
}

//�f�[�^�x�[�X�ɐڑ�
$conn = oci_connect("photo_retrieval","mS6EqirX","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//�p�X���[�h��ύX���邩�ǂ����ŕ���(update�̕����ς��)
IF($pass_change_flug == "true"){
	//�t���O��true�̏ꍇ�͌��݂̃p�X���[�h���m�F����ׂ̖₢���킹���s��
	//sql���̍쐬
	$sql = "SELECT password FROM photo_operation.user_table WHERE user_name = '" . $_SESSION['user_name'] . "'";

	//SQL�������s���A���s���ʂ�$stid�Ɋi�[
	$stid = oci_parse($conn, $sql);
	oci_execute($stid);

	//���s���ʂ̔z���$row�֊i�[
	$row = oci_fetch_array($stid, OCI_NUM);

	//���݂̃p�X���[�h�ƃt�H�[���œ��͂��ꂽ�p�X���[�h����v���Ă��邩�m�F����
	IF($row[0] <> $now_password){
		//��v���Ȃ������ꍇ�͂����ŏ����I��
		//�߂�l�Ƃ���"password_mismatch"�ƕԂ�
		exit("password_mismatch");

	}else{
		//��v�����ꍇ�͏����𑱍s
		$sql_parts = "password = '" . $new_password . "', ";
	}
}else{
	//pass_change_flug��false(�p�X���[�h��ύX���Ȃ�)�̏ꍇ�͈ȉ�
	$sql_parts = "";

}

//sql���̍쐬
$sql = "UPDATE user_table SET " . $sql_parts . "slide_speed = '" . $slide_speed . "', secret_status = '" . $secret_status . "' WHERE user_name = '" . $_SESSION['user_name'] . "'";

//����p�̃��[�U�Ńf�[�^�x�[�X�ɐڑ�
$conn = oci_connect("photo_operation","sZ9KXhF4","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//SQL�������s���A���s���ʂ�$stid�Ɋi�[
$stid = oci_parse($conn, $sql);
oci_execute($stid);

//�߂�l�Ƃ���"success"�ƕԂ��ď����I��
echo "success";

?>