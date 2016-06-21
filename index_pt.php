<?php

/**
*
* JSON INFO
*
*/
$ch =  curl_init("http://" . $_SERVER[HTTP_HOST] . "/service/projetos");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$json_info = curl_exec($ch);
$json_info = json_decode($json_info, TRUE);


/**
*
* Obtem os destaques ativos baseado no idioma
*
*/
$ch =  curl_init("http://" . $_SERVER[HTTP_HOST] . "/service/destaques/1/pt");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$json_destaques = curl_exec($ch);
$json_destaques = json_decode($json_destaques, TRUE);


/**
*
* Obtem as categorias ativas baseado no idioma
*
*/
$ch =  curl_init("http://" . $_SERVER[HTTP_HOST] . "/service/categorias/1/pt");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$json_categorias = curl_exec($ch);
$json_categorias = json_decode($json_categorias, TRUE);	

$length = sizeof($json_categorias['res']);
$json_categorias_e =  array();
$json_categorias_j =  array();

for ($i=0; $i < $length  ; $i++) {
	$obj = $json_categorias['res'][$i];
	if($obj['tipo'] == 'E'){
		array_push($json_categorias_e , $obj);
	}else{
		array_push($json_categorias_j , $obj);
	}
}

$currentURL = 'http://' . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI];
$title = "GAEL – Grupo de Ativações e Experiências Live";
$description = "Grupo de Ativações e Experiências Live A agência que inova na relação entre marcas e consumidores. - Tel.: (11) 2395-4400";
?>

<!DOCTYPE HTML>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]-->
	<head>
		<title><?php echo $title?></title>

		<!-- -->

		<!-- metas -->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=1000">
		<meta name="description" content="<?php echo $description?>">
		<meta name="keywords" content="Live, Live Marketing, Ativação, Experiências de marca, Ao vivo, Ação de Marketing, Case de Marketing, Case publicitário, Agência de comunicação, Ideias inovadoras, Estratégia de marketing, Estratégia de comunicação, Criação publicitária, Advertising, Campanha publicitária, Comunicação, Publicidade, Propaganda, Publicitário, Planejamento, Evento, Ponto de Venda, Promoção,  Promocional, Projeto Especial, Público-alvo, Target">

		<!-- tags facebook -->
		<meta property="og:title" content="<?php echo $title ?>" />
		<meta property="og:description" content="<?php echo $description ?>" />
		<meta property="og:image" content="<?php echo $currentURL?>images/share.png" />
		<meta property="og:url" content="<?php echo $currentURL ?>" />
		<meta property="og:type" content="website" />

		<!-- stylesheets -->
		<link rel="stylesheet" href="css/main.css" type="text/css">

		<!-- Define o idioma -->
		<script>
			var LANG = '<?= $_SESSION['lang'] ?>';
			var XPATH = '<?= $_SERVER[HTTP_HOST] ?>';
		</script>

		<!-- fallback -->
		<!--[if IE]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	</head>
	<body>
		<!--MENU-->
		<div class="menu">

			<div id="bg-menu">
			</div>

			<div id="container-header" class="container clearfix">
				<div id="logo" class="grid_3">
					<a href="index.html" class="logo-gael"><img src="svg/logo-large.svg" alt="GAEL"/></a>
				</div>
				<div class="grid_9 omega">
					<div id="nav">
						<ul class="navigation">
							<li class="btn-clients" data-slide="1">
								<a href="#clients"><i><img src="svg/ico-clients.svg" alt="Clients"/></i></a>
							</li>
							<li class="btn-work" data-slide="2">
								<a href="#work"><i><img src="svg/ico-work.svg" alt="Work"/></i></a>
							</li>
							<li class="btn-about" data-slide="3">
								<a href="#about"><i><img src="svg/ico-about.svg" alt="About"/></i></a>
							</li>
							<li class="btn-contact" data-slide="4">
								<a href="#contact"><i><img src="svg/ico-contact.svg" alt="Contact"/></i></a>
							</li>
							<li class="btn-contact" data-slide="4">
								<a href="index.php?lang=en">
									<i><img src="images/lang/bt_en_peq.png" alt="Language" style="width: 26px; height: 18px; margin-top: 2px" /></i>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>

		</div>

		<!--CLIENTS-->
		<div class="slide" id="clients" data-slide="1" data-stellar-background-ratio="0.5">
			<!--MENU HOME-->
			<div class="ct-menu-home">
				<div id="menu-home" class="container">
					<ul id="nav-home" class="navigation">
						<li class="btn-h-clients" data-slide="1">
							<a href="#clients">
								<i><img src="svg/ico-clients.svg" width="48" height="30" alt="Clients"/></i>
							<span>NOVIDADES</span></a>
						</li>
						<li id="btn-work" class="btn-h-work" data-slide="2">
							<a href="#work">
								<i><img src="svg/ico-work.svg" width="34" height="42" alt="Work"/></i>
							<span>CASES</span></a>
						</li>
						<li class="btn-h-about" data-slide="3">
							<a href="#about">
								<i><img src="svg/ico-about.svg" width="35" height="35" alt="About"/></i>
							<span>SOBRE</span></a>
						</li>
						<li class="btn-h-contact" data-slide="4">
							<a href="#contact">
								<i><img src="svg/ico-contact.svg" width="40" height="35" alt="Contact"/></i>
							<span>CONTATO</span></a>
						</li>
						<li class="btn-h-contact" data-slide="4" style="padding-top: 14px">

							<style>
								.xx-custom-menu {
									display: inline !important;
								}
								.xx-custom-menu-lang {
									display: inline !important;
									width: 45px;
									margin: 5px;
									opacity: .5;
								}
								.xx-custom-menu-lang.xx-this, .xx-custom-menu-lang:hover {
									opacity: 1;
								}
							</style>		

							<a href="index.php?lang=pt" class="xx-custom-menu">
								<img src="images/lang/bt_pt_peq.png" class="xx-custom-menu-lang xx-this">
							</a>							
							<a href="index.php?lang=en" class="xx-custom-menu">
								<img src="images/lang/bt_en_peq.png" class="xx-custom-menu-lang">
							</a>
							<a href=""><span>LANGUAGE</span></a>
						</li>
					</ul>
				</div>
			</div>
			<!--BANNERS-->
			<div class="ct-banner">
				<div class="ct-itens">
					<?php
					for ($i = 0; $i < $json_destaques["qtd"]; $i++) {

						$idcase = (isset($json_destaques["res"][$i]["caseid"]) ) ? $json_destaques["res"][$i]["caseid"] : -1;
						echo "<div class='banner' data-name='" .$idcase . "'>";
						echo "<div class='label' style='background-image: url(" . $json_destaques["res"][$i]["imagem"] . ")'></div>";
						echo "<div class='background' data-stellar-background-ratio='0.5' style='background-image: url(" . $json_destaques["res"][$i]["texto"] . ")'></div></div>";
					}
					?>
				</div>
			</div>
			<!--NAVIGATION BANNERS-->
			<div id="navigation-banner">
				<div class="container">
					<ul>
						<?php
						for ($i = 0; $i < $json_destaques["qtd"]; $i++) {
							echo "<li class='btn-banner'><span class='circle'></span></li>" ;
						}
						?>
					</ul>
				</div>
			</div>
		</div>		

		<!--WORK-->
		<div class="slide" id="work" data-slide="2" data-stellar-background-ratio="0.5">

			<div id="container-work">

				<!--HOME WORK-->
				<div id="container-work-home">
					<!--no indenting - white space between display elements-->
					<div class="line">
						<div class="cel cel-50 nowork cel-frist">
							<h2><img class="tlt-work" src="svg/tlt-work.svg" alt="Work"/></h2>
							<div class="combobox">
								<div class="combobox-field"> Filtrar </div>
								<div class="combobox-content">
									<div class="combobox-content-data">
										<a href="#" class="see-all active">Ver Todos</a>
										<ul>
											<?php
											$length = sizeof($json_categorias_e);
											for ($i = 0; $i < $length; $i++) {
												$item = $json_categorias_e[$i];
												echo '<li><a href="#" data-id-categoria="'. $item['id'] .'">'. $item['nome'] .'</a></li>' ;
											}
											?>
										</ul>
										<ul>
											<?php
											$length = sizeof($json_categorias_j);
											for ($i = 0; $i < $length; $i++) {
												$item = $json_categorias_j[$i];
												echo '<li><a href="#" data-id-categoria="'. $item['id'] .'">'. $item['nome'] .'</a></li>' ;
											}
											?>
										</ul>
									</div>
								</div>
							</div>

						</div>
						<div class="cel cel-25" id="item-5" data-effect="true"></div>
						<div class="cel cel-25 nowork">
							<div class="paginate">
								<div id="previous">
									<a href="#" ><img src="svg/work/icon-arrow-left.svg" alt="Voltar"/></a>
								</div>
								<div id="next">
									<a href="#"><img src="svg/work/icon-arrow-right.svg" alt="Voltar"/></a>
								</div>

							</div>
						</div>
					</div>

					<div class="line">
						<div class="cel cel-25" id="item-6" data-effect="true"></div>
						<div class="cel cel-25" id="item-2" data-effect="true"></div>
						<div class="cel cel-25" id="item-1" data-effect="true"></div>
						<div class="cel cel-25 nowork"></div>
					</div>

					<div class="line">
						<div class="cel cel-25 nowork"></div>
						<div class="cel cel-25" id="item-3" data-effect="true"></div>
						<div class="cel cel-25" id="item-4" data-effect="true"></div>
						<div class="cel cel-25" id="item-7" data-effect="true"></div>
					</div>
				</div>
				<div id="wrap-job">
					<?php
					for ($i = 0; $i < $json_work["qtd"]; $i++) {

						echo"<div class='container-job' data-name='" . $json_work["res"][$i]["id"] . "'>
							<div class='job-main'>
								<div class='box-info'>
									<div class='job-info'>
										<div class='btn-close-job'>
											<i><img src='images/ico-back.png' alt='Voltar'/></i> VOLTAR
										</div>
										<h3>" . $json_work["res"][$i]["titulo"] . "</h3>
											<div class='call'>" . $json_work["res"][$i]["descricao"] . "</div>
										<hr/>
											<div class='text'>
												<p>" . $json_work["res"][$i]["texto"] . "</p>
											</div>
									</div>
									<div class='btn-more'>
										<i><img src='svg/ico-more.svg' alt='Ver mais'/></i>
									</div>
								</div>
								<nav class='nav-jobs'>
									<div class='nav-prev'></div>
									<div class='nav-next'></div>
								</nav>
							</div>
							<div class='job-assets'>";
								for ($j = 1; $j < 6; $j++) {
									if ($json_work["res"][$i]["imagem_integra" . $j]!=null){
										echo "<div class='j-content' style='background-image: url(" . $json_work["res"][$i]["imagem_integra" . $j] . ")'></div>";
									}
								}
							echo"</div>
						</div>";
					}
					?>
				</div>

			</div>
		</div>

		<!--ABOUT-->
		<div class="slide" id="about" data-slide="3" data-stellar-background-ratio="0.5">
			<div class="container clearfix">
				<script>
					window.videoUrl = '<?php echo $json_info["res"]["video"]; ?>';
				</script>

				<div class="grid_12">
					<h2><img src="svg/tlt-sobre.svg" alt="About"/></h2>
					<hr/>
					<div class="container-steps">
						<div class="step1" id="step1">
							

							<?php 
							/**							
							*
							* Obtem o sobre
							*
							*/

							$sobre = file_get_contents('http://' . $_SERVER[HTTP_HOST] . '/service/web/uploads/sobre/sobre.json');							
							$sobre = (array) json_decode($sobre);

							if(!empty($sobre[$lang]->arquivo))
							{							
								$sobre[$lang]->arquivo = 'http://' . $_SERVER[HTTP_HOST] . '/service/web/uploads/pdf/' . $sobre[$lang]->arquivo;								
							}
							?>

							<div class="title"><?= stripslashes($sobre[$lang]->titulo) ?></div>
							<div class="sub-title"><?= stripslashes($sobre[$lang]->subtitulo) ?></div>
							<div class="button-know hidden-mobile">Clique e conheça</div>


							<?php if(!empty($sobre[$lang]->arquivo)) { ?>
							<br>
							<br>
							<a href="<?= $sobre[$lang]->arquivo ?>" class="button-know hidden-mobile" target="_blank">Baixe nossa apresentação</a>
							<?php } ?>

							<div class="thumb-video hidden-mobile"><img src="images/about/video.png" alt="Video" /></div>
						</div>
						<div class="step2" id="step2">
							<div class="back"></div>
							<div class="movie">

							</div>

						</div>
						<div class="step3" id="step3">
							<div class="back"></div>
							<div class="title"><?= stripslashes($sobre[$lang]->titulo) ?></div>
							<div class="sub-title"><?= stripslashes($sobre[$lang]->subtitulo) ?></div>
							<div class="text"> 
								<?php echo str_ireplace(array('<div>', '</div>'), array('<p>', '</p>'),$sobre[$lang]->texto); ?>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>


		<div class="slide" id="contact" data-slide="4">
			<div class="container clearfix">

				<div class="grid_12">
					<h2><img src="svg/tlt-contato.svg" alt="Contact"/></h2>
					<hr/>
					<form id="form-padrao" class="container-contact">
						<div class="ct-animation">
							<div class="icon-success">
								<img src="svg/ico-sucesso.svg" alt="Sua mensagem foi enviada"/>
								<p>Sua mensagem foi enviada</p>
							</div>
							<div class="ct-field">
								<label for="fullname">NOME</label>
								<div class="input-small">
									<input id="fullname" name="nome" type="text"/>
								</div>
							</div>
							<div class="ct-field">
								<label for="email">EMAIL</label>
								<div class="input-small">
									<input id="email" name="email" type="text"/>
								</div>
							</div>
							<div class="ct-field">
								<label for="mind">MENSAGEM</label>
								<div class="input-large">
									<textarea name="descricao" id="mind" cols="30" rows="10"></textarea>
									<button type="submit" id="btn-enviar">ENVIAR</button>
								</div>
							</div>
						</div>
						<ul>
							<li class="i-email">
								<div class="bg-ico"><i><img src="svg/ico-email.svg" alt="E-mail"/></i></div>

								<?php
								$count = 1;
								$emails = explode(';', $json_info["res"]["contato_email"]);
								foreach($emails as $email) {
									if(!empty($email)) {
								?>									
									<a href="mailto:<?= $email ?>" style="font-size: 20px; <?= ($count == 1 ? 'padding-left: 0px;' : 'padding-left: 45px; margin-top: -20px; display: block;') ?> ">
										<?= $email ?> 
									</a>
								<?php } $count++; } ?>
								<br>
							</li>
							<li class="i-telefone">
								<div class="bg-ico"><i><img src="svg/ico-telefone.svg" alt="Telefone"/></i></div>

								<?php
								$count = 1;
								$fones = explode(';', $json_info["res"]["contato_fone"]);
								foreach($fones as $fone) {
									if(!empty($fone)) {
								?>	
									<a href="" style="font-size: 20px; <?= ($count == 1 ? 'padding-left: 0px;' : 'padding-left: 45px; margin-top: -20px; display: block;') ?> ">
										<?= $fone ?> 
									</a>
								<?php } $count++; } ?>
							</li>
							<li class="i-markee">
								<div class="bg-ico"><i><img src="svg/ico-markee.svg" alt="Localização"/></i></div>
								<?php
								/**
								* O primeiro item é a filial 1 e o segundo é a filial 2
								*/
								$count = 1;
								$enderecos = explode(';', $json_info["res"]["contato_endereco"]);
								foreach($enderecos as $endereco) {
									if(!empty($endereco)) {
								?>										
									<a href="<?= ( $count == 1 ? 'https://www.google.com/maps/place/Rua+Jaceru,+115+-+Vila+Gertrudes/@-23.6211575,-46.6936248,17z/data=!3m1!4b1!4m2!3m1!1s0x94ce50c14b0ed4b3:0x5a302c013000b08f' : 'https://www.google.com/maps/place/Av.+Rio+Branco,+1+-+Centro,+Rio+de+Janeiro+-+RJ,+Brasil/@-22.8974141,-43.182405,17z/data=!3m1!4b1!4m5!3m4!1s0x997f5a3945c06b:0x931e31dd184aa0c7!8m2!3d-22.8974191!4d-43.1802163') ?>" target="_blank" style="font-size: 20px; <?= ($count == 1 ? 'padding-left: 0px;' : 'padding-left: 45px; margin-top: -20px; display: block;') ?>">
										<?= $endereco ?>
									</a>
								<?php } $count++; } ?>

							</li>
							<li class="i-facebook">
								<div class="bg-ico"><i><img src="svg/ico-facebook.svg" alt="Localização"/></i></div>
								<a href="https://www.facebook.com/agenciagael" target="_blank" style="font-size: 20px;">
									/AGENCIAGAEL
								</a>
							</li>
							<li class="i-instagram">
								<div class="bg-ico"><i><img src="svg/ico-instagram.svg" alt="Localização"/></i></div>
								<a href="https://instagram.com/gael.ag" target="_blank" style="font-size: 20px;">
									@GAEL.AG
								</a>
							</li>
						</ul>
					</form>
				</div>

			</div>
		</div>

		<!--VENDORS-->
		<script src="https://www.youtube.com/player_api" type="text/javascript"></script>
		<script src="//f.vimeocdn.com/js/froogaloop2.min.js"></script>
		<script type="text/javascript" src="vendor/vendor.min.js"></script>

		<!--SCRIPTS-->
		<script type="text/javascript" src="js/templates/template.js"></script>
		<script type="text/javascript" src="js/templates/work.thumbs.js"></script>
		<script type="text/javascript" src="js/templates/work.job.js"></script>

		<script type="text/javascript" src="js/component/paginate.js"></script>
		<script type="text/javascript" src="js/component/thumb.js"></script>
		<script type="text/javascript" src="js/component/navigation.thumbs.js"></script>
		<script type="text/javascript" src="js/component/job.js"></script>
		<script type="text/javascript" src="js/component/navigation.jobs.js"></script>

		<script type="text/javascript" src="js/combobox.js"></script>
		<script type="text/javascript" src="js/works.js"></script>
		<script type="text/javascript" src="js/scripts.js"></script>
	</body>

</html>
