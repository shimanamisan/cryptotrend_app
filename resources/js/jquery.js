$(function () {
  /****************************************
フラッシュメッセージの表示
*****************************************/
  var $jsFlashMsg = $('.js-flash-msg');
  var msg = $jsFlashMsg.text();
  if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
    $jsFlashMsg.slideToggle('slow');
    setTimeout(function(){
      $jsFlashMsg.slideToggle('slow');
    }, 5000);
  }

  /****************************************
Twitterアカウントの新規登録画面へ遷移する
*****************************************/
  let $jsRedirect = $('#js-redirect');
  $jsRedirect.on('click', function (e) {
    e.preventDefault;
    let checked = confirm(
      'Twitterアカウントの登録画面へ移動します。\n よろしいですか？'
    );
    if (checked == true) {
      return true;
    } else {
      return false;
    }
  });
});
