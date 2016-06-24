var wW;
var wH;
var areaAtual = "#clients";
var menuHeight;
var currentItem;
var currentJobGallery = 0;
var jobDescription;
var currentJobImg;
var jobOpened = false;
var tBanner;
var nBanners;
var durationBanner = 7000;
var currentBannerNum = 0;
var keyNavStatus = true;
var timeoutBlockKeys = 1000;

jQuery(document).ready(function ($) {

	window.location.hash = "";

	wW = $(window).width();
	wH = $(window).height();

	/*INIT FRAMEWORK*/
	$(window).stellar({
		horizontalScrolling: false,
		verticalOffset: 0
	});

	$('#nav').ddscrollSpy({
		scrolltopoffset: -60,
		scrollduration: 1300
	});

	$('#nav-home').ddscrollSpy({
		scrolltopoffset: -60,
		scrollduration: 1300
	});

	$("#nav li, #btn-work").click(function(){
		$(window).trigger('work.close.gallery');
		// closeGallery(currentJobGallery);
	});

	/*END FRAMEWORK*/

	/********************************************************
	 * CLIENTS
	 ********************************************************/

	//Banner
	nBanners = $(".ct-itens>.banner").length;
	$(".banner:eq(0)").css("display", "block");
	activateNavItem(0);
	initBanner();

	//Overs btns banner
	$(".btn-banner").on("mouseenter", function() {
		activateNavItem($(this).index());
	});

	$(".btn-banner").on("mouseleave", function() {
		var numItem = $(this).index();
		if (numItem!=currentBannerNum){
			deactivateNavItem(numItem);
		}
	});

	$(".btn-banner").click(function() {
		var numItem = $(this).index();
		if (numItem !=currentBannerNum){
			changeBanner(numItem);
			initBanner();
		}
	});

	$(".ct-banner").on('click', '.banner', function() {
		var item = $(this).data('name') || 0;

		if(item === -1) return;
		$(window).trigger('work.close.gallery', [false] );
		// closeGallery(currentJobGallery, false);
		$(window).trigger('work.open.gallery', [item, false] );
		// openGallery(item, false);

		goToByScroll(2);
	});

	/********************************************************
	 * CONTACT
	 ********************************************************/
	$("#contact li").on("mouseenter", function(){
		TweenMax.to($(this).find("img"), .3, {
			scale: 1.2,
			ease: Quart.easeOut
		});
	});
	$("#contact li").on("mouseleave", function(){
		TweenMax.to($(this).find("img"), .3, {
			scale: 1,
			ease: Quart.easeOut
		});
	});

	// Validacao e envio
	$("#form-padrao").validate({
		rules: {
			nome: {
				required: true,
				minlength: 3
			},
			email: {
				required: true,
				email: true
			},
			descricao: {
				required: true
			}
		},
		messages: {
			nome: "Insira seu nome.",
			email: "E-mail inválido.",
			descricao: "Mensagem inválida."
		}
	});

	jQuery('#form-padrao').submit(function(){
		if ($("#form-padrao").valid() == true){

			var dados = jQuery( this ).serialize();

			jQuery.ajax({
				type: "POST",
				url: "http://homologacao.gael.ag/service/contatos",
				data: dados,
				success: function( data )
				{
					animationSuccess();
				}
			});

			return false;a
		}

	});

	// Validacao e envio (Ingles)
	$("#form-padrao-ingles").validate({
		rules: {
			nome: {
				required: true,
				minlength: 3
			},
			email: {
				required: true,
				email: true
			},
			descricao: {
				required: true
			}
		},
		messages: {
			nome: "Name is invalid",
			email: "E-mail is invalid",
			descricao: "Message is invalid"
		}
	});

	jQuery('#form-padrao-ingles').submit(function(){
		if ($("#form-padrao-ingles").valid() == true){

			var dados = jQuery( this ).serialize();

			jQuery.ajax({
				type: "POST",
				url: "http://homologacao.gael.ag/service/contatos",
				data: dados,
				success: function( data )
				{
					animationSuccess();
				}
			});

			return false;a
		}

	});

	/********************************************************
	 * GENERAL
	 ********************************************************/

	$(".logo-gael").click(function(e){
		e.preventDefault();
		$(".btn-clients>a").click();
	});

	$(window).resize(function(){
		resizeActions();
	});

	//Menu show/hide
	var menu = false;
	$(window).scroll(function(){
		var scrollPos = $(document).scrollTop();

		if(scrollPos>50 && menu==false){
			menu = true;
			showMenu();
		} else  if(scrollPos<50 && menu==true){
			menu = false;
			hideMenu();
		}
	});

	resizeActions();
	//setNavKeys();

	/********************************************************
	 * About
	 ********************************************************/
	var about = $('#about'),
		containerSteps = about.find('.container-steps'),
		step1 = containerSteps.find('#step1'),
		step2 = containerSteps.find('#step2'),
		step3 = containerSteps.find('#step3'),
		movie = step2.find('.movie'),
		current = step1,
		iFrame = '<iframe id="palyer" src="'+ window.videoUrl + '?api=1&color=FFCE14" width="960" height="540" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen ></iframe>';


	function animateSteps(newStep , direction , cbFinish){
		if(current === newStep){
			return false;
		}

		TweenMax.to(current, 1, {
				left: ((direction == -1) ? '100%' : '-100%' ),
				opacity: 0,
				ease: Quart.easeOut,
				onComplete: function(){
					$(this).css( 'display', 'none');
					if(!!cbFinish)cbFinish();
				}
			});

		newStep.css({
			'display': 'block'	,
			'left': ((direction == -1) ? '-100%' : '100%' )
		});

		TweenMax.to(newStep, 1, {
				left: 0,
				opacity: 1,
				ease: Quart.easeOut,
			});

		current = newStep;

	}

	step1.find('.button-know').on('click', function(){
		animateSteps(step3, -1);
	});

	step1.find('.thumb-video').on('click', function(){
		movie.html(iFrame);
		animateSteps(step2 , 1);
	});

	step2.find('.back').on('click', function(){
		animateSteps(step1, -1, function (){
			movie.html('');
		});
	});

	step3.find('.back').on('click', function(){
		animateSteps(step1, 1);
	});

	// window.onload = function() {
	// 	areaAtual = window.location.hash;

	// 	switch(areaAtual){
	// 		case "#clients":
	// 			$(".btn-h-clients>a").click();
	// 			console.log("#clients");
	// 			removeOvers();
	// 			break;
	// 		case "#work":
	// 			$(".btn-h-work>a").click();
	// 			console.log("#work");
	// 			removeOvers();
	// 			break;
	// 		case "#about":
	// 			$(".btn-h-about>a").click();
	// 			console.log("#about");
	// 			removeOvers();
	// 			break;
	// 		case "#contact":
	// 			$(".btn-h-contact>a").click();
	// 			console.log("#contact");
	// 			break;
	// 		default:
	// 			$(".btn-h-clients>a").click();
	// 			console.log("#clients");
	// 			removeOvers();
	// 			break;
	// 	}

	// };

});

function animationSuccess(){
	TweenMax.to($(".ct-field").eq(2), .5, {
		marginLeft: 600,
		ease: Quart.easeInOut
	});
	TweenMax.to($(".ct-field").eq(1), .5, {
		marginLeft: 600,
		ease: Quart.easeInOut,
		delay:.2
	});
	TweenMax.to($(".ct-field").eq(0), .5, {
		marginLeft: 600,
		ease: Quart.easeInOut,
		delay:.4,
		onComplete: function(){
			$(".icon-success").css("display", "block");
			TweenMax.to($(".icon-success"), .5, {
				scale: 1,
				ease: Quart.easeInOut
			});
		}
	});
	TweenMax.to($(".icon-success"), 0, {
		scale: 0
	});
}

function goToByScroll(dataslide) {

	$("html, body").animate({
		scrollTop: $('.slide[data-slide="' + dataslide + '"]').offset().top-60
	}, 1300, 'easeInOutQuart');
}

function resizeActions(){

	menuHeight = $("#bg-menu").height();
	wW = $(window).width();
	wH = $(window).height() - menuHeight;

	$("#clients").css("height", wH + menuHeight);

	if($("#about").height() < wH) {
		$("#about, #contact").css("height", wH);
	}

}

/*INIT BANNER */
function initBanner(){

	clearInterval(tBanner);

	tBanner = setInterval(function(){

		if (currentBannerNum < (nBanners-1)){

			changeBanner(currentBannerNum+1);
		} else {
			changeBanner(0);
		}

	}, durationBanner);
}

function changeBanner(bannerNum){

	var currentBanner = $(".banner:eq(" + currentBannerNum + ")");
	currentBanner.css("zIndex", "10");
	deactivateNavItem(currentBannerNum);

	var nextBanner = $(".banner:eq(" + bannerNum + ")");
	currentBanner.css({
		"zIndex": 0
	});
	nextBanner.css({
		"display": "block",
		"zIndex": 10
	});
	TweenMax.from(nextBanner.find(".label"),.8, {
		left: wW,
		ease: Quart.easeInOut,
		delay: .15
	});
	var lastBanner = currentBanner;
	currentBannerNum = bannerNum;
	TweenMax.from(nextBanner.find(".background"),.8, {
		left: wW,
		ease: Quart.easeInOut,
		onComplete: function(){
			resizeActions();
			lastBanner.css({
				"display": "none"
			});
		}
	});
	activateNavItem(bannerNum);
}
/*END BANNER */


function showMenu() {
	TweenLite.to($("#bg-menu"), .5, {
		top: 0,
		ease:Quart.easeOut
	});
	TweenLite.to($(".logo-gael>img"), .5, {
		scale: .43,
		marginTop: -47,
		ease:Quart.easeOut,
		delay: .1,
		onComplete: function(){
			$("#logo").css("height", 60);
		}
	});

	if (isIE()){
		TweenLite.to($(".btn-clients"), .4, {marginTop: 0,ease:Quart.easeOut});
		TweenLite.to($(".btn-work"), .4, {marginTop: 0,ease:Quart.easeOut});
		TweenLite.to($(".btn-about"), .4, {marginTop: 0,ease:Quart.easeOut});
		TweenLite.to($(".btn-contact"), .4, {marginTop: 0,ease:Quart.easeOut});
	} else {
		TweenLite.to($(".btn-clients"), .4, {marginTop: 0,ease:Quart.easeOut,delay: 0.05});
		TweenLite.to($(".btn-work"), .4, {marginTop: 0,ease:Quart.easeOut,delay: 0.15});
		TweenLite.to($(".btn-about"), .4, {marginTop: 0,ease:Quart.easeOut,delay: 0.25});
		TweenLite.to($(".btn-contact"), .4, {marginTop: 0,ease:Quart.easeOut,delay: 0.35});
	}

	$(".menu").css("pointer-events", "auto");
}

function hideMenu() {
	$("#logo").css("height", 150);
	TweenLite.to($(".logo-gael>img"),.5, {
		scale: 1,
		marginTop: 0,
		ease:Expo.easeOut,
		delay: 0
	});
	if (isIE()){
		TweenLite.to($("#bg-menu"), .4, {top: -60,ease:Quart.easeOut,delay: .25});
		TweenLite.to($(".btn-clients"), .5, {marginTop: -60,ease:Quart.easeOut});
		TweenLite.to($(".btn-work"), .5, {marginTop: -60,ease:Quart.easeOut});
		TweenLite.to($(".btn-about"), .5, {marginTop: -60,ease:Quart.easeOut});
		TweenLite.to($(".btn-contact"), .5, {marginTop: -60,ease:Quart.easeOut});
	} else {
		TweenLite.to($("#bg-menu"), .4, {top: -60,ease:Quart.easeOut,delay: .5});
		TweenLite.to($(".btn-clients"), .2, {marginTop: -60,ease:Quart.easeOut,delay: .3});
		TweenLite.to($(".btn-work"), .2, {marginTop: -60,ease:Quart.easeOut,delay: .2});
		TweenLite.to($(".btn-about"), .2, {marginTop: -60,ease:Quart.easeOut,delay: .1});
		TweenLite.to($(".btn-contact"), .2, {marginTop: -60,ease:Quart.easeOut,delay: 0});
	}
	$(".menu").css("pointer-events", "none");

}

function activateNavItem (pNum) {
	pNum ++;
	TweenMax.to($("#navigation-banner li:nth-child(" + (pNum) + ")").find('.circle'), .25, {
		width: "11px",
		height: "11px",
		marginTop: "-2px",
		borderColor: "#FFFFFFF",
		ease: Back.easeOut
	});
}

function deactivateNavItem (pNum) {
	pNum ++;
	TweenMax.to($("#navigation-banner li:nth-child(" + (pNum) + ")").find(".circle"),.15, {
		width: "6px",
		height: "6px",
		marginTop: "0",
		borderColor: "#F0E221",
		ease: Linear.easeNone
	});
}

//Navigation keys


function setNavKeys(){
	$(document).on("keydown", function(e){

		areaAtual = (!window.location.hash ? "#clients" : window.location.hash );

		if (keyNavStatus) {

			if(areaAtual!="#contact"){
				e.preventDefault();
			}

			//Disable key nav for 2 seconds
			keyNavStatus = false;
			setTimeout(function(){keyNavStatus = true}, timeoutBlockKeys);

			if (e.keyCode == 37){

				if (areaAtual=="#clients"){
					if (currentBannerNum > 0){
						changeBanner(currentBannerNum-1);
					} else {
						changeBanner(nBanners-1);
					}
				}
				if(areaAtual=="#work" && jobOpened){
					if (currentJobImg > 0){
						var setaNav = $(".nav-jobs .nav-prev");
						if (jobDescription == true){$(".container-job[data-name='" + currentJobGallery + "'] .btn-more").click();};
						prevJobImg(currentJobImg);
						TweenMax.to(setaNav, .3, {
							scale: .65,
							ease:Quint.easeOut
						});
						TweenMax.to(setaNav, .3, {
							scale: 1,
							ease:Quint.easeOut,
							delay: .5
						});
					}
				}

				//RIGHT
			} else if (e.keyCode == 39){

				if (areaAtual=="#clients"){
					if (currentBannerNum < (nBanners-1)){
						changeBanner(currentBannerNum+1);
					} else {
						changeBanner(0);
					}
				}

				if(areaAtual=="#work" && jobOpened){
					if (currentJobImg < nJobsImg) {
						var setaNav = $(".nav-jobs .nav-next");
						if (jobDescription == true){$(".container-job[data-name='" + currentJobGallery + "'] .btn-more").click();};
						currentJobImg = currentJobImg+1;
						nextJobImg(currentJobImg);
						TweenMax.to(setaNav, .3, { scale: .65, ease:Quint.easeOut });
						TweenMax.to(setaNav, .3, { scale: 1, ease:Quint.easeOut, delay: .5 });
					}
				}

				//UP
			} else if (e.keyCode == 38){

				switch(areaAtual){
					case "#clients":
						$(".btn-clients>a").click();
						break;
					case "#work":
						$(".btn-clients>a").click();
						break;
					case "#about":
						$(".btn-work>a").click();
						removeOvers();
						break;
					case "#contact":
						$(".btn-about>a").click();
						break;
					default:
						break;
				}

				//DOWN
			} else if (e.keyCode == 40){

				switch(areaAtual){
					case "#clients":
						$(".btn-work>a").click();
						removeOvers();
						break;
					case "#work":
						$(".btn-about>a").click();
						break;
					case "#about":
						$(".btn-contact>a").click();
						break;
					case "#contact":
						$(".btn-clients>a").click();
						break;
					default:
						break;
				}

				//BACKSPACE
			} else if (e.keyCode == 8){

				if(areaAtual=="#work" && jobOpened){
					$(window).trigger('work.close.gallery');
					// closeGallery(currentJobGallery);
				}

			} else {
				if(areaAtual=="#work" && jobOpened == false && returnValidNumFromKey(e.keyCode) != undefined){
					var validNum = parseInt(returnValidNumFromKey(e.keyCode))-1;
					var element = $("div[data-name='" + validNum + "']").find(".w-over");

					TweenMax.to(element, .3, { opacity: 0, ease:Quint.easeOut });
					TweenMax.to(element, .2, {
						opacity: 1,
						ease:Quint.easeOut,
						delay: .3,
						onComplete:function(){
							$(window).trigger('work.open.gallery', [validNum] );
							// openGallery(validNum);
						}
					});
				}
			}

		}//keyNavStatus

	});
}

function returnValidNumFromKey (nKey) {
	var n = nKey;
	if (n==49||n==97){
		return 1;
	} else if (n==50||n==98){
		return 2;
	} else if (n==51||n==99){
		return 3;
	} else if (n==52||n==100){
		return 4;
	} else if (n==53||n==101){
		return 5;
	} else if (n==54||n==102){
		return 6;
	} else if (n==55||n==103){
		return 7;
	}
}

function isIE () {
	var myNav = navigator.userAgent.toLowerCase();
	return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
}

//Mobile detect
var ua = navigator.userAgent.toLowerCase();
var uMobile = '';
uMobile = '';
uMobile += 'blackberry;iphone;ipad;ipod;windows phone;android;iemobile 8';

v_uMobile = uMobile.split(';');

var boolMovel = false;
for (i=0;i<=v_uMobile.length;i++)
{
	if (ua.indexOf(v_uMobile[i]) != -1)
	{
		boolMovel = true;
	}
}

var pageWork = new Work($('#work'));
pageWork.init();



Job($('#wrap-job .text')).reset();