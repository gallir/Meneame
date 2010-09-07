$(function(){
	$("h1 > *, h4 > a, h5 > p:first, h5 > a, .topcommentsli > li > a , li > strong > a, p").each(function(index) {
		$(this).html(to_hoygan($(this).html()));
	});
});


function to_hoygan(str) 
{
	str=str.replace(/á/gi, 'a');
	str=str.replace(/é/gi, 'e');
	str=str.replace(/í/gi, 'i');
	str=str.replace(/ó/gi, 'o');
	str=str.replace(/ú/gi, 'u');

	str=str.replace(/igo(\s|$)/gi, 'ijo$1');
	str=str.replace(/yo/gi, 'io');
	str=str.replace(/ y /gi, ' i ');
	str=str.replace(/ que /gi, ' q ');
	str=str.replace(/ hay /gi, ' ai ');
	str=str.replace(/m([pb])/gi, 'n$1');
	str=str.replace(/qu([ei])/gi, 'k$1');
	str=str.replace(/ct/gi, 'st');
	str=str.replace(/cc/gi, 'cs');
	str=str.replace(/ch/gi, 'x');
	str=str.replace(/ll([aeou])/gi, 'y$1');
	str=str.replace(/ya/gi, 'ia');
	str=str.replace(/yo/gi, 'io');
	str=str.replace(/g([ei])/gi, 'j$1');
	str=str.replace(/^([aeiou][a-z]{3,})/gi, 'h$1');
	str=str.replace(/ ([aeiou][a-z]{3,})/gi, ' h$1');
	str=str.replace(/[zc]([ei])/gi, 's$1');
	str=str.replace(/z([aou])/gi, 's$1');
	str=str.replace(/c([aou])/gi, 'k$1');

	str=str.replace(/b([aeio])/gi, 'vvv;$1');
	str=str.replace(/v([aeio])/gi, 'bbb;$1');
	str=str.replace(/vvv;/gi, 'v');
	str=str.replace(/bbb;/gi, 'b');

	str=str.replace(/oi/gi, 'oy');
	str=str.replace(/xp([re])/gi, 'sp$1');
	str=str.replace(/es un/gi, 'esun');
	str=str.replace(/en el/gi, 'enel');
	str=str.replace(/(^| )h([ae]) /gi, '$1$2 ');
	str=str.replace(/(^| )h([aeiou][aeiou])/gi, '$1$2');
	str=str.replace(/aho/gi, 'ao');
	str=str.replace(/a ver /gi, 'haber ');
	str=str.replace(/ por /gi, ' x ');
	str=str.replace(/ñ/gi, 'ny');
	str=str.replace(/buen/gi, 'GÜEN');

        // benjami
	str=str.replace(/windows/gi, 'güindous');
	str=str.replace(/we/gi, 'güe');
	// str=str.replace(/'. '/gi, '');
	str=str.replace(/,/gi, ' ');
	str=str.replace(/ r([aeiou])/gi, ' rr$1');
	str=str.toUpperCase();
	// Recuepra HTML
	str=str.replace(/&NBSP;/g, '&nbsp;');
	str=str.replace(/&ANP;/g, '&amp;');
	return str;
}
