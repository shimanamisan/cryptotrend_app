$(function () {
    /****************************************
フラッシュメッセージの表示
*****************************************/
    let $jsFlashMsg = $(".js-flash-msg");
    let msg = $jsFlashMsg.text();
    if (msg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
        $jsFlashMsg.slideToggle("slow");
        setTimeout(function () {
            $jsFlashMsg.slideToggle("slow");
        }, 5000);
    }

    /****************************************
SPメニューを開閉するアクション
*****************************************/
    let $jsSpmenu = $("#js-spmenu-trigger");
    let $jsSpNav = $("#js-spnav-trigger");
    $jsSpmenu.on("click", function () {
        $jsSpmenu.toggleClass("active"); // ハンバーガーメニューの描画を変えるクラス
        $jsSpNav.toggleClass("p-header--isActive"); // SPメニューを横から表示するクラス
    });

    /****************************************************************************
関連アカウント表示時に、Twitterアカウント未登録ユーザーを新規登録画面へ誘導する
*****************************************************************************/
    let $jsRedirect = $("#js-redirect");
    $jsRedirect.on("click", function (e) {
        e.preventDefault;
        let checked = confirm(
            "Twitterアカウント認証画面へ移動します。\n よろしいですか？"
        );
        if (checked == true) {
            return true;
        } else {
            return false;
        }
    });
    /****************************************
スクロールアニメーション
*****************************************/
    $(window).scroll(function () {
        scroll_animation();
    });

    function scroll_animation() {
        let $fadeNode = $(".js-scroll");
        $fadeNode.each(function () {
            // $fadeNodeの要素の位置を取得
            let elementTop = $(this).offset().top;
            // 画面一番上からのスクロール量を取得
            let scroll = $(window).scrollTop();
            // 画面の高さを取得
            let windowHeight = $(window).height();
            if (scroll > elementTop - windowHeight) {
                $(this).addClass("c-anime__fadein");
            }
        });
    }

    /****************************************
かんたんログイン
*****************************************/
    let $inputEmail = $(".js-guest-email");
    let $inputPass = $(".js-guest-password");
    let $guestBtn = $(".js-guest-login");

    $guestBtn.on('click', function(e){
        e.preventDefault();
        $inputEmail.val("test01@mail.com")
        $inputPass.val("password")
    })
});
