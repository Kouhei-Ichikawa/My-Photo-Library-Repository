<?php

//�Z�b�V�����̐錾
session_start();

$date = $_POST['name1'];

//�f�[�^�x�[�X�ɐڑ�
$conn = oci_connect("photo_retrieval","mS6EqirX","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//�V�[�N���b�g�t���O���B���ݒ肾�����ꍇ�ASQL���Ńt���OON�̎ʐ^���q�b�g���Ȃ��悤�ɂ���
IF($_SESSION['secret_status'] == 'hidden'){
	$secret_flug = ' AND secret_flug = 0';
}else{
	$secret_flug = '';
}

//sql���̍쐬
$sql = "SELECT COUNT(file_pass) FROM(SELECT * FROM photo_operation.photo_table WHERE filming_date = '" . $date . "' AND user_name = '" . $_SESSION['user_name'] . "'" . $secret_flug . ")";

//SQL�������s���A���s���ʂ�$stid�Ɋi�[
$stid = oci_parse($conn, $sql);
oci_execute($stid);

//���s���ʂ̔z���$row�֊i�[
$row = oci_fetch_array($stid, OCI_NUM);

//���ʂ�\��(�߂�l�Ƃ���javascript�֕Ԃ�)
echo $row[0];

?>