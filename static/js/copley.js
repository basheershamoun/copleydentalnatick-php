// JavaScript Document
function ActivarSidebarButton(){
    $('#side-bar-button').click(function(e){
        e.preventDefault();
				$('#side-bar-menu').fadeToggle(300);
    });
}
function HabilitarCombos(){
	$('#menu-cp-combo>select').change(function(){
		var aux = $('#menu-cp-combo>select>option:selected').attr('value');
		if(aux != ''){
			window.location.href = aux;
		}
	});
	$('#main-menu-links-combo>select').change(function(){
		var aux = $('#main-menu-links-combo>select>option:selected').attr('value');
		if(aux != ''){
			window.location.href = aux;
		}
	});
	$('#footer-links-combo>select').change(function(){
		var aux = $('#footer-links-combo>select>option:selected').attr('value');
		if(aux != ''){
			window.location.href = aux;
		}
	});
	$('#contactanos-combo>select').change(function(){
		var aux = $('#contactanos-combo>select>option:selected').attr('value');
		if(aux != ''){
			window.location.href = aux;
		}
	});

}
$(document).ready(function(){
	ActivarSidebarButton();
	HabilitarCombos();
});

$(window).load(function(){
});