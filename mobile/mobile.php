<?php
  ini_set("display_errors", 0);

  // LOCAL - comentar quando for para prod
  // $_SERVER[HTTP_HOST] = "gael.ag";
  $_SERVER[HTTP_HOST] = "gael.ag";

  //JSON INFO
  $ch =  curl_init("http://" . $_SERVER[HTTP_HOST] . "/service/projetos");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $json_info = curl_exec($ch);
  $json_info = json_decode($json_info, TRUE);

  //JSON DESTAQUES
  $ch =  curl_init("http://" . $_SERVER[HTTP_HOST] . "/service/destaques/1");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $json_destaques = curl_exec($ch);
  $json_destaques = json_decode($json_destaques, TRUE);

  //JSON CASES
  $ch =  curl_init("http://" . $_SERVER[HTTP_HOST] . "/service/cases/1");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $json_cases = curl_exec($ch);
  $json_cases = json_decode($json_cases, TRUE);
  $length_cases = sizeof($json_cases['res']);

  $currentURL = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  $title = "GAEL – Grupo de Ativações e Experiências Live";
  $description = "Grupo de Ativações e Experiências Live A agência que inova na relação entre marcas e consumidores. - Tel.: (11) 2395-4400";

  $largura = (int)$_GET['largura'];
?>

<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $title?></title>
    <meta name="description" content="<?php echo $description?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta property="og:title" content="<?php echo $title?>">
    <meta property="og:description" content="<?php echo $description?>">
    <meta property="og:image" content="<?php echo $currentURL?>images/share.png">
    <meta property="og:url" content="<?php echo $currentURL?>">
    <meta property="og:type" content="website">

    <script>
      var urlHost = '<?php echo $_SERVER[HTTP_HOST]; ?>';
    </script>

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="stylesheet" href="bower_components/nprogress/nprogress.css">

    <link rel="stylesheet" href="styles/main.css">

    <script src="bower_components/modernizr/modernizr.js"></script>
    <script src="bower_components/nprogress/nprogress.js"></script>
  </head>
  <body>
    <main id="all">
      <header id="header" class="js-header">
        <div class="icon-menu js-btn-menu"></div>
        <nav class="menu js-menu">
          <ul>
            <li class="wrap-item custom-icon-megaphone js-nav-menu" data-scroll-nav="0"><a href="javascript:void(0)" class="item">Novidades</a></li>
            <li class="wrap-item custom-icon-lamp js-nav-menu" data-scroll-nav="1"><a href="javascript:void(0)" class="item">Cases</a></li>
            <li class="wrap-item custom-icon-smile js-nav-menu" data-scroll-nav="2"><a href="javascript:void(0)" class="item">Sobre</a></li>
            <li class="wrap-item custom-icon-balloon js-nav-menu" data-scroll-nav="3"><a href="javascript:void(0)" class="item">Contato</a></li>
          </ul>

          <div class="outside js-outside"></div>
        </nav>

        <h1 class="logo">
          <figure class="fix-logo">
            <img src="images/logo.png" alt="Gael">
          </figure>
        </h1>
      </header> <!-- header -->

      <script>

        NProgress
          .configure( {
            minimum: 0.08,
            easing: 'ease',
            positionUsing: '',
            speed: 200,
            trickle: true,
            trickleRate: 0.02,
            trickleSpeed: 800,
            showSpinner: true,
            barSelector: '[role="bar"]',
            spinnerSelector: '[role="spinner"]',
            parent: '#header',
            template: '<div class="" role="bar"><div class="peg"></div></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
          } );

        NProgress.start();

      </script>

      <section id="news" data-scroll-index="0">

        <ul class="slider single-item-1">
          <?php
              for ($i = 0; $i < $json_destaques["qtd"]; $i++) {


                switch($largura) {
                  case $largura >= 0 and $largura <= 640:
                    $Imagem = "imagem_640";
                    break;

                  case $largura >= 640 AND $largura <= 1024:
                    $Imagem = "imagem_1024";
                    break;

                  case $largura >= 1024:
                    $Imagem = "imagem_1920";
                    break;

                }

                $idcase = (isset($json_destaques["res"][$i]["caseid"]) ) ? $json_destaques["res"][$i]["caseid"] : -1;
                echo "<li class='new js-new' data-category-id='" . $idcase . "' data-scroll-goto='1'>";
                // echo "<div class='label' style='background-image: url(" . $json_destaques["res"][$i]["imagem"] . ")'></div>";
                echo "<figure class='crop'><img src='" . $json_destaques["res"][$i][$Imagem] . "'></figure></div>";

              }
            ?>
        </ul>

      </section> <!-- news -->

      <div class="wrap-case js-wrap-case" data-scroll-index="1">
        <section id="gallery" class="js-gallery">
          <ul class="slider single-item-2">

            <script>
              var Cases = <?php echo json_encode($json_cases["res"], TRUE); ?>;
              var screenWidth = <?php echo $largura; ?>;
            </script>

            <?php

              echo '<li>';

              for ($i = 0, $count_cases = 0; $i < $length_cases; $i++, $count_cases++) {

                if( $count_cases == 4 ) {

                  $count_cases = 0;

                  echo '</li><li>';

                }

                $item = $json_cases['res'][$i];

                echo '<article class="case js-thumb-case" data-category-id="'. $item['id'] .'" data-scroll-goto=1">';
                echo   '<figure class="thumb">';
                echo     '<img src="'. $item['imagem_thumb'] .'" alt="">';
                echo   '</figure>';

                echo   '<div class="info">';
                echo     '<h3 class="title">'. $item['titulo'] .'</h3>';
                echo     '<p class="subtitle">'. $item['descricao'] .'</p>';
                echo   '</div>';
                echo '</article>';

              }

              echo '</li>';

            ?>
          </ul>
        </section> <!-- gallery -->

        <section id="case" class="js-case">
          <div class="back js-back" data-scroll-goto="1">voltar</div>
          <ul class="slider list-cases js-list-cases"></ul>

          <!-- <div class="btn-info js-btn-info" data-scroll-goto="1"></div> -->

         <!--  <div class="info-case js-info-case">

            <div class="btn-back-info js-btn-back-info"></div>

            <div class="wrap-content js-wrap-content">
              <div class="content js-content">
                <div class="fix-content">
                  <h3 class="js-title"></h3>

                  <div class="js-text"></div>
                </div>
              </div> -->
<!--               <h3 class="js-title"></h3>

              <div class="js-text"></div> -->
            <!-- </div> -->

          </section></div>
         <!-- case -->
       <!-- wrap-case -->

      <section id="about" data-scroll-index="2">
        <h2 class="title">
          <figure>
            <img class="img" src="images/svg/tlt-about.svg" alt="">
          </figure>
        </h2>

        <p class="format-1">
          A GAEL não é uma agência de comunicação.
        </p>

        <p class="format-2">
          é um grupo de ativação e
          experiências live.
        </p>

        <style>

        </style>

        <div class="wrap-video">
          <!-- <video class="video js-video" x-webkit-airplay="allow" preload="" poster="">
            <source type="video/youtube" src="https://www.youtube.com/watch?v=YiL2gqKEQMI"></source>
          </video> -->
          <!-- <video class="video js-video" x-webkit-airplay="allow" preload="" src="http://pdl.vimeocdn.com/49455/243/239577790.mp4?token2=1414002570_6489dcf5cb2801ca736c2304e15c4b9f&amp;aksessionid=3ab8877b62c915ce" poster=""></video> -->

          <!-- <div class="wrap-btn js-btn-video">
            <button class="btn-play js-btn-play"></button>
          </div> -->
          <!-- <iframe src="http://www.youtube.com/embed/dFVxGRekRSg" frameborder="0" width="560" height="315"></iframe> -->

          <iframe src="<?php echo $json_info['res']['video']; ?>?api=1&color=FFCE14" width="960" height="540" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>
        </div>
      </section>

      <section id="contact" data-scroll-index="3">
        <h2 class="title">
          <figure>
            <img class="img" src="images/svg/tlt-contact.svg" alt="">
          </figure>
        </h2>

        <form action="" class="form-default js-form-contact">
          <fieldset class="js-wrap-content-form">
            <legend>Contato</legend>
            <div class="line">
              <label for="name" class="lbl">Nome</label>
              <input type="text" name="nome" id="name" class="field-0">

              <span class="warn js-warn"><small class="js-warn-text"></small>  <small class="icon-attention"></small></span>
            </div>
            <div class="line">
              <label for="email" class="lbl">Email</label>
              <input type="email" name="email" id="email" class="field-0">

              <span class="warn js-warn"><small class="js-warn-text"></small>  <small class="icon-attention"></small></span>
            </div>
            <div class="line">
              <label for="message" class="lbl">Mensagem</label>
              <textarea name="descricao" id="message" class="field-1"></textarea>

              <span class="warn js-warn"><small class="js-warn-text"></small> <small class="icon-attention"></small></span>
            </div>
            <div class="line">
              <input type="submit" class="btn js-submit" value="enviar">
            </div>
          </fieldset>
          <div class="wrap-response-status js-wrap-response-status"></div>
        </form>

        <ul class="wrap-contact-type">
          <li class="type js-type left">
            <div class="icon-balloon-2 js-btn-contact" id="balloon" data-scroll-goto="10"></div>
          </li>
          <li class="type js-type left-25">
            <div class="icon-phone js-btn-contact" id="phone" data-scroll-goto="10"></div>
          </li>
          <li class="type js-type center">
            <div class="icon-pin js-btn-contact" id="pin" data-scroll-goto="10"></div>
          </li>
          <li class="type js-type right-25">
            <div class="icon-facebook js-btn-contact" id="facebook" data-scroll-goto="10"></div>
          </li>
          <li class="type js-type right">
            <div class="icon-instagram js-btn-contact" id="instagram" data-scroll-goto="10"></div>
          </li>
        </ul>

        <ul class="wrap-contact-content-type" data-scroll-index="10">
          <li class="content js-contact-content" id="content-balloon">
            <a class="inner" href="mailto:<?php echo $json_info['res']['contato_email']; ?>"><?php echo $json_info["res"]["contato_email"]; ?></a>
          </li>
          <li class="content js-contact-content" id="content-phone">
            <a class="inner" href="tel:<?php echo $json_info['res']['contato_fone']; ?>"><?php echo $json_info["res"]["contato_fone"]; ?></a>
          </li>
          <li class="content js-contact-content" id="content-pin">
            <a class="inner" href="https://www.google.com/maps/place/Rua+Jaceru,+115+-+Vila+Gertrudes/@-23.6211575,-46.6936248,17z/data=!3m1!4b1!4m2!3m1!1s0x94ce50c14b0ed4b3:0x5a302c013000b08f" target="_blank"><?php echo $json_info["res"]["contato_endereco"]; ?></a>
          </li>
          <li class="content js-contact-content" id="content-facebook">
            <a class="inner" href="https://www.facebook.com/agenciagael?fref=ts" target="_blank">/AGENCIAGAEL</a>
          </li>
          <li class="content js-contact-content" id="content-instagram">
            <a class="inner" href="https://instagram.com/gael.ag" target="_blank">@GAEL.AG</a>
          </li>
        </ul>
      </section>
    </main>

    <script>
      var vartop = {};
    </script>

    <script src="scripts/vendor.js"></script>

    <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
    <script>
      (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
      function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
      e=o.createElement(i);r=o.getElementsByTagName(i)[0];
      e.src='//www.google-analytics.com/analytics.js';
      r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
      ga('create','UA-XXXXX-X');ga('send','pageview');
    </script>

    <script src="scripts/main.js"></script>

    <script>

      window.onload = function() {

        NProgress.done();

        NProgress
          .configure( {
            template: '<div class="" role="bar"><div class="peg"></div></div><span class="lbl-load">Enviando</span><div class="spinner txt" role="spinner"><div class="spinner-icon"></div></div>'
          } );

      };

      $(document).ready(function() {

        $('.single-item-1').slick({
            dots: true,
            infinite: true,
            speed: 300,
            // autoplay: true,
            lazyLoad: false,
            slidesToShow: 1,
            slidesToScroll: 1,

            swipe: true,

            slide: 'li',
            // autoplaySpeed: 6000,
            prevArrows: '<button type="button" class="slick-prev arrow-left">Previous</button>',
            nextArrows: '<button type="button" class="slick-next arrow-right">Next</button>'
        });

        $('.single-item-2').slick({
            dots: true,
            infinite: true,
            speed: 300,
            // autoplay: true,
            lazyLoad: false,
            slidesToShow: 1,
            slidesToScroll: 1,

            swipe: false,

            slide: 'li',
            // autoplaySpeed: 6000,
            prevArrows: '<button type="button" class="slick-prev arrow-left">Previous</button>',
            nextArrows: '<button type="button" class="slick-next arrow-right">Next</button>'
        });

      });
    </script>

  </body>
</html>
