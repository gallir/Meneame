<?
	include('config.php');
	include(mnminclude.'html1.php');
	include(mnminclude.'link.php');
	do_header(_('FAQ'));
	do_banner_top();
	echo '<div id="container-wide">' . "\n";
?>
<h2 class="faq-title">Las preguntas presuntamente frecuentes</h2>
<div id="faq-contents">
<ol>
<li>
<h4>¿Qué es menéame?</h4>
<p>Es un web que te permite enviar una historia que será revisada por todos y será promovida, o no, a la página principal. Cuando un usuario envía una noticia ésta queda en la <a href="shakeit.php"><em>cola de pendientes</em></a> hasta que reúne los votos suficientes para ser promovida a la página principal.
También encontrarás más información, dudas, recomendaciones en el <a href="http://meneame.wikispaces.com/" title="wiki meneame">wiki del menéame</a>.
</p>
</li>

<li>
<h4>¿Hace falta registrarse?</h4>
<p>Sólo es necesario hacerlo para enviar historias y agregar comentarios.
</p>
</li>


<li>
<h4>¿Cómo promover las historias?</h4>
<p>Selecciona la opción <a href="shakeit.php"><em>menear noticias</em></a> y te aparecerán las noticias no publicadas, ordenadas descendentemente por fecha de envío. Sólo tienes que "menear" aquellas que más te agradan o consideres importantes. Una vez superado unos umbrales de votos y <em>karma</em> serán promovidas a la página principal.</p>
<p>No te olvides de leer las <a href="http://meneame.net/libs/ads/legal-meneame.php">condiciones de uso</a>.</p>
</li>


<li>
<h4>¿Qué es ese formulario <em>¿problemas?</em> que me aparece cuando voy a menear noticias pendientes?</h4>
<p>Es un formulario para indicar que una noticia es duplicada, <em>spam</em>, provocación o simple basura. Dichos reportes son votos negativos a la noticia, no abuses de él. Los votos que reúnan un mínimo de votos contarios serán movidos a una cola diferente.</p>
</li>


<li>
<h4>¿Sólo cuenta el número de votos?</h4>
<p>No, cuentan también el <em>karma</em>, si es voto anónimo o no, y el número de <em>problemas</em> reportados (similar a votos negativos). El algoritmo es bastante complejo, <a href="http://svn.meneame.net/index.cgi/branches/version2/scripts/promote7.php">promote7.php</a>.
</p>
</li>


<li>
<h4>¿Cómo enviar historias?</h4>
<p>Debes <a href="register.php">registrarte</a> antes, es muy fácil y rápido. Luego seleccionas <a href="submit.php"><em>enviar historia</em></a>. En un proceso de tres pasos simples la historia será enviada a la cola de pendientes. Por supuesto, te recomendamos que votes por tu historia, por algo la has puesto :-).
</p>
</li>


<li>
<h4>¿Qué tipos de historias hay que enviar?</h4>
<p>Las que tu desees, pero sigue leyendo. Estarán sujetas a la revisión de los lectores que las votarán o no. Aún así, el objetivo principal es que se traten de noticias y apuntes de blogs. Lo que <strong>no debes hacer es <em>autobombo</em> o <em>spam</em></strong>, es decir enviar demasiados enlaces con el objetivo de promocionar tu propio <em>blog</em> (o el de tus amigos), envía historias que puedan ser interesantes para muchos. Ya sabemos que si lo escribes es porque lo consideras relavente para tí, pero puede no serlo para la audiencia de menéame. Además, si todos hiciesen lo mismo, habría decenas de miles de noticias para votar, lo que haría imposible el funcionamiento de la idea. Usa el sentido común, intenta pasar un <em>cromo</em> interesante o que alegre a la gente. No mires sólo tu ombligo. Sólo usa el <strong>sentido común y un mínimo de espíritu colaborativo y respeto hacia los demás</strong>. Es muy recomendable leer la <a href="http://meneame.wikispaces.com/Meneatiqueta">"Meneatiqueta"</a> en el wiki, redactada y mejorada con el aporte de los usuarios.
</p>
</li>

<li>
<h4>¿Cómo funciona eso de los votos y el karma?</h4>
<p>En el wiki está <a href="http://meneame.wikispaces.com/Karma">perfectamente explicado</a>.</p>
</li>

<li>
<h4>¿Cómo se seleccionan las noticias que se publican en la portada?</h4>
<p>Lo hace un proceso que se ejecuta cada cinco minutos.</p>

<p>Primero calcula cuál es el karma mínimo que tienen que tener las noticias. Este valor depende de la media del karma de las noticias que fueron promovidas en las últimas dos semanas, más un coeficiente que depende del tiempo transcurrido desde la publicación de la última noticia. Este coeficiente decrece a medida que pasa el tiempo y se hace uno (1) cuando ha pasado una hora. Eso quiere decir que pasada una hora, cuando el coeficiente se hizo uno, cualquier noticia que tenga un karma igual o superior a la media será promovida. Esto tiene dos objetivos, por un lado se persigue que si la <em>calidad</em> es constante se promoverá una media de una noticia por hora, pero las que reciban más votos (se espera que sea incremental) serán publicadas antes.
</p>

<p>El karma de cada noticia se calcula multiplicando el número de votos por el karma del autor del voto. Si es anónimo ese voto vale cuatro (4), si es de un usuario registrado el valor es multiplicado por su karma.</p>

<p>Finalmente hay una restricción adicional para evitar <em>abusos</em> de los usuarios registrados: sólo pueden ser promovidas aquellas noticias que al menos tengan <em>N</em> votos. Donde <em>N</em> actualmente es cinco (5).</p>
</li>


<li>
<h4>¿Qué es esa pestaña "recomendadas" en la página de votación de pendientes?</h4>
<p>Muestra las noticias de autores que están más cercanos al perfil del usuario que vota. Se usan algoritmos de redes sociales (en particular de "afiliación" sobre grafos bi-partitos) para mostrar primero las noticias que más se aproximan a los gustos de cada usuario. Así se evita tener que definir "amigos" manualmente. Este algoritmo está sujeto a constantes mejoras y ajustes, su buen funcionamiento depende de la cantidad y "calidad" de los votos de cada usuario.
</p>
</li>


<li>
<h4>¿Qué es esa pestaña "descartadas" en la página de votación de pendientes?</h4>
<p>Cuando una noticia recibe más reportes de "problemas" que votos positivos, es movida a esta cola. Los usuarios pueden seguir votando y si consigue los votos suficientes volverá a la cola de pendientes normal.
</p>
</li>

<li>
<h4>¿Qué es el nótame?</h4>
<p>Una herramienta de comunicación entre los usuarios de menéame y se organiza en pequeños apuntes, como los mini-post de un blog colectivo --de todos los usuarios de nótame-- y a la vez individual.  Puedes usarlo para cuestiones relacionadas con menéame o para explicar lo que quieras.  Puedes escribir desde el web, mensajería instantánea o el movil.  Encontrarás <a href="http://meneame.wikispaces.com/N%C3%B3tame">más detalles en el wiki</a>.</p>
</li>

<li>
<h4>¿Qué es la fisgona?</h4>
<p>Muestra lo que sucede en menéame en tiempo real.  Si eres usuario registrado también puedes usarla para chatear.</p>
</li>

<li>
<h4>¿No es machista, eso de llamarle «fisgona» (o «la jefa»)?</h4>
<p>Primero se llamó «el fisgón» (y «¡el jefe!» la pantalla de «la fisgona» para usar en el trabajo). Había mujeres que opinaban que era machista poner siempre nombres genéricos masculinos y les hicimos caso: cambiamos a «la fisgona» y «¡la jefa!».  Ahora hay quien piensa que esto también es machista.  Si no tiene solución ¿para qué solucionarlo?</p>
</li>


<li>
<h4><a name="we"></a>¿Quién está detrás del menéame?</h4>
<p>Comenzó como un proyecto personal y <em>amateur</em> de <a href="http://ricardogalli.com/">Ricardo Galli</a>, con la colaboración de <a href="http://blog.bitassa.cat">Benjamí Villoslada</a>. Ambos son socios fundadores de <strong>Menéame Comunicacions S.L</strong> (con sede en Sineu, Mallorca), la responsable legal y fiscal actual de los sitios web <a href="http://meneame.net/">meneame.net</a>, <a href="http://mueveme.net/">mueveme.net</a> y <a href="http://notame.net">notame.net</a>.
</p>
<p>
Encontrarás los datos de <strong>contacto</strong> en <a href="http://meneame.net/libs/ads/legal-meneame.php#contact">la página de condiciones legales</a>.
</p>
</li>


<li>
<h4>¿Por qué? ¿para qué?</h4>
<p>Está explicado en  el apunte <a href="http://mnm.uib.es/gallir/posts/2005/12/08/535/"><em>¿Qué y porqué el menéame?</em></a> del blog de Ricardo Galli.</p>
</li>

<li>
<h4>¿Por qué es tan similar a Digg?</h4>

<p>Porque era un buen punto de partida, la interfaz e interacción con el usuario era simple y efectiva. ¿Porqué reinventar la rueda desde cero si ya está bastante redonda?.</p>

<p>De todas formas el objetivo del menéame es distinto. No sólo se dan cuenta los que pueden votar anónimamente, también los autores de los apuntes enlazados que reciben notificación inmediata (<em>trackbacks</em>) y especialmente los autores que envian noticias. Estos últimos notan las diferencias fundamentales, y cómo está todo pensado para facilitar el <em>meneos</em> de blogs más que de sitios genéricos.</p>

<p>El objetivo fundamental y diferencias con Digg están explicadas en <a href="http://mnm.uib.es/gallir/posts/2005/12/08/535/"><em>¿Qué y porqué el menéame?</em></a></p>
</li>


<li>
<h4>¿Cuales son las diferencias fundamentales con Digg y otros servicios similares?</h4>
<ul>

<li>Se permiten votos anónimos.</li>

<li>La publicación de la noticia no sólo está basada en los votos (meneos), sino en el valor del karma de los usuarios que han votado.</li>

<li>El sistema está específicamente programado para interactuar vía <em>trackbacks</em> con los sistemas de <em>blogs</em> existentes. En la mayoría de los casos detecta automáticamente las direcciones de <em>trackback</em>.</li>

<li>Hay diversos RSS, casi para todos los gustos, incluso de búsquedas personalizadas.</li>

</ul>

</li>


<li>
<h4>¿Qué software se usa?</h4>
<p>El software está completamente desarrollado por Ricardo Galli. Está programado enteramente en PHP y MySQL.</p>
</li>

<li>
<h4>¿Será liberado el software?</h4>

<p><a href="http://mnm.uib.es/gallir/posts/2005/12/12/541/">Ya está liberado</a>. En el pie de todas las páginas encontrarás el enlace para descargarlo. Tiene la licencia <a href="http://www.affero.org/oagpl.html">Affero General Public License</a>.</p>
</li>



<li>
<h4>¿Dónde notificamos errores, problemas o sugerencias?</h4>
<p>Ver la <a href="http://meneame.net/libs/ads/legal-meneame.php#contact">sección de contacto</a> en la condiciones legales y de uso.
</p>
</li>


<li>
<h4>¿Dónde podemos seguir la evolución de los cambios al menéame?</h4>
<p>En <a href="http://svn.meneame.net">el SVN</a>.</p>
</li>

<li>
<h4>Dices que el software no está acabado, ¿cuando lo estará?</h4>
<p><strong>Nunca</strong>. Un programa nunca está acabado. A nosotros nos está resultando divertido hacer estos programas que nos ayuda a resolver un problema inherente de los blogs personales, y al mismo tiempo pasarnos pipas implementando chorradas que no tienen sentido o son impensables en la actual web donde predomina el <em>eBusiness</em>.
</p>

<p>Las funcionalidad básicas están acabadas, faltan detalles, como las características que <a href="http://meneame.wikispaces.com/Sugerencias" title="en el wiki">solicitan los usuarios</a>.</p>

</li>


<li>
<h4>¿Cómo pensáis pagar los gastos?</h4>
<p>Ya hay mucho tráfico, AdSense cubre los gastos y nos alcanzó para comprarnos varios Ferraris y un Panda de los nuevos, así que por ahora no hay peligro.</p>
</li>

<li>
<h4>¿Porqué una elefanta en el logo?</h4>
<pre>
    Un elefante se <strike>balanceaba</strike> meneaba
    sobre la tela de una araña
    Como veía que no se caía
    fue a buscar otro elefante.

    Dos elefantes se meneaban
    sobre la tela de una araña
    Como veían que no se caían
    Fueron a buscar otro elefante
</pre>
</li>

</ol>
<p>Para más información, consulta al <a href="http://blog.meneame.net">blog</a> o al <a href="http://meneame.wikispaces.com/" title="wiki meneame">wiki del menéame</a>.</p>

</div>
<?

	do_footer();
?>
