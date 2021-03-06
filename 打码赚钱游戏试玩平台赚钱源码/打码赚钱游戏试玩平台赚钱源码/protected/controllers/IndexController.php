<?php

/*
 * 首页
 */

class IndexController extends Controller {
    /*
     * 首页
     */

    function actionShow() {
        $this->renderPartial('show');
    }

    /*
     * 登录
     */

    function actionLogin() {
        $mem_login = new MemLoginForm();
        if (isset($_POST['MemLoginForm'])) {
            $mem_login->attributes = $_POST['MemLoginForm'];
            $mem_login->pwd = $_POST['MemLoginForm']["pwd"] . "wp"; //老平台密码加入wp
            if ($mem_login->validate() && $mem_login->login()) {
                $result = "success";
            } else {
                $mem_login->pwd = $_POST['MemLoginForm']["pwd"];
            }
        }
        $this->renderPartial('login', array('mem_login' => $mem_login, "result" => $result), '', $processOutput = TRUE);
    }

    /*
     * 注册
     */

    function actionRegester() {
        $men_model = new Mem();
        if (isset($_POST['Mem'])) {
            foreach ($_POST['Mem'] as $_k => $_v) {
                $men_model->$_k = strip_tags($_v);
            }
            $men_model->pwd = $_POST['Mem']["pwd"] . "wp"; //老平台密码加入wp
            $men_model->pwd2 = $_POST['Mem']["pwd2"] . "wp"; //老平台密码加入wp
            $men_model->memimg_id = rand(1, 18); //随机分配头像
            $men_model->ip = $_SERVER['REMOTE_ADDR']; //存入注册的ip
            if ($men_model->save()) {
                $men_model->login();
                $system_info = System::model()->findByPk(1);
                $hlb = $system_info["zc_rewards"]; //注册奖励
                $memid = $men_model["id"];
                $title = "成功注册、网络平台";
                $content = $title . "，获得" . $hlb . "元宝红包奖励";
                $this->updhlb($hlb, 22, $content, $memid, 0); //元宝
                if (!empty($hlb)) {
                    $this->sendmessage($title, $content, 1, $memid);
                }
                Yii::app()->user->setFlash('zcstart', 'success');
                $this->redirect("show");
            } else {
                $men_model->pwd = $_POST['Mem']["pwd"]; //老平台密码加入wp
                $men_model->pwd2 = $_POST['Mem']["pwd2"]; //老平台密码加入wp
            }
        }
        $mem_login = new MemLoginForm();
        if (isset($_POST['MemLoginForm'])) {
            $mem_login->attributes = $_POST['MemLoginForm'];
            $mem_login->pwd = $_POST['MemLoginForm']["pwd"] . "wp"; //老平台密码加入wp
            if ($mem_login->validate() && $mem_login->login()) {
                $this->redirect("show");
            } else {
                $mem_login->pwd = $_POST['MemLoginForm']["pwd"];
            }
        }
        $this->renderPartial('regester', array('men_model' => $men_model, 'mem_login' => $mem_login, 'windows' => $_POST['windows']), '', $processOutput = TRUE);
    }

    /*
     * 忘记密码：步骤1
     */

    function actionPwdfind1() {
        $email_model = Email::model();
        if (isset($_POST['Email'])) {
            foreach ($_POST['Email'] as $_k => $_v) {
                $email_model->$_k = strip_tags($_v);
            }
            if ($email_model->validate()) {
                $this->redirect("pwdfind2/email/" . $email_model['email']);
            }
        }
        $this->renderPartial('pwdfind1', array("email_model" => $email_model), '', $processOutput = TRUE);
    }

    /*
     * 忘记密码：步骤2
     */

    function actionPwdfind2() {
        $code_model = Code::model();
        if (isset($_POST['Code'])) {
            foreach ($_POST['Code'] as $_k => $_v) {
                $code_model->$_k = strip_tags($_v);
            }
            if ($code_model->validate()) {
                $this->redirect(SITE_URL . "index/pwdfind3/email/" . $_GET["email"]);
            }
        }
        $this->renderPartial('pwdfind2', array("code_model" => $code_model, "num" => $_POST['Code']["num"]));
    }

    /*
     * 忘记密码：步骤3
     */

    function actionPwdfind3() {
        $changepwd_model = Changepwd::model();
        if (isset($_POST['Changepwd'])) {
            foreach ($_POST['Changepwd'] as $_k => $_v) {
                $changepwd_model->$_k = strip_tags($_v);
            }
            if ($changepwd_model->validate()) {
                $info = Mem::model()->findBySql("select * from {{mem}} where email='" . $_GET["email"] . "'");
                $info['pwd'] = md5($changepwd_model['pwd'] . "wp");
                $info->update();
                $this->redirect(SITE_URL . "index/pwdfind4");
            }
        }
        $this->renderPartial('pwdfind3', array("changepwd_model" => $changepwd_model));
    }

    /*
     * 忘记密码：步骤4
     */

    function actionPwdfind4() {

        $this->renderPartial('pwdfind4');
    }

    /*
     * 验证码-发送邮件
     */

    function actionSendemail() {
        $email = $_POST['email'];
        $Num = $_POST['Num'];
        $message = '、玩友,您本次操作的验证码是：' . $Num;
        $mailer = Yii::createComponent('application.extensions.mailer.EMailer');
        $mailer->Host = 'smtp.chaoyouwan.com';  //smtp服务器的名称（这里以qq邮箱为例）
        $mailer->IsSMTP();  // 启用SMTP
        $mailer->SMTPAuth = TRUE; //启用smtp认证
        $mailer->From = 'service@chaoyouwan.com';   //发件人地址（也就是你的邮箱地址）
        $mailer->AddReplyTo('service@chaoyouwan.com'); //回复地址(可填可不填)
        $mailer->AddAddress($email); //添加收件人
        $mailer->FromName = '、客服系统';  //发件人姓名
        $mailer->Username = 'service@chaoyouwan.com';    //这里输入发件地址的用户名
        $mailer->Password = 'Qq.123545';    //这里输入发件地址的密码
        $mailer->SMTPDebug = true;   //设置SMTPDebug为true，就可以打开Debug功能，根据提示去修改配置
        $mailer->CharSet = 'UTF-8'; //设置邮件编码
        $mailer->Subject = Yii::t('、玩友，找回密码', '验证码!');  //邮件主题
        $mailer->Body = $message;  //邮件内容
        $mailer->Send();
    }

    /*
     * 退出
     */

    function actionLogout() {
        Yii::app()->user->logout($destroySession = FALSE);
        $this->redirect(SITE_URL);
    }

    /*
     * 协议
     */

    function actionXieyi() {

        $this->renderPartial('xieyi');
    }

    /*
     * 提现协议
     */

    function actionTxxieyi() {

        $this->renderPartial('txxieyi');
    }

    /*
     * 关于我们
     */

    function actionGuangyu() {

        $this->renderPartial('guangyu');
    }

    /*
     * 联系我们
     */

    function actionLianxi() {

        $this->renderPartial('lianxi');
    }


	
	
    /*
     * 违规处理
     */

    function actionWeigui() {

        $this->renderPartial('weigui');
    }

}
