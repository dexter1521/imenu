<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="es-MX">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>iMenu — Menú digital en minutos</title>
	<meta name="description" content="Crea tu menú digital en minutos. Sin app, sin complicaciones. Plataforma hecha en México para restaurantes, cafés y hoteles." />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--primary: #0057B7;
			/* Azul digital */
			--accent: #FF6B35;
			/* Coral CTA */
			--mint: #A8E6CF;
			/* Verde menta suave (éxitos) */
			--dark: #2F2F2F;
			/* Gris carbón */
			--light: #F5F6FA;
			/* Blanco humo */
			--radius: 16px;
			--shadow: 0 10px 30px rgba(0, 0, 0, .08);
		}

		* {
			box-sizing: border-box
		}

		html,
		body {
			margin: 0;
			padding: 0;
			color: var(--dark);
			background: #fff;
			font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif
		}

		a {
			color: inherit;
			text-decoration: none
		}

		img {
			max-width: 100%;
			display: block
		}

		.container {
			width: min(1120px, 92%);
			margin-inline: auto
		}

		.btn {
			display: inline-flex;
			align-items: center;
			gap: .6rem;
			padding: .9rem 1.15rem;
			border-radius: 12px;
			font-weight: 600;
			border: 2px solid transparent;
			transition: .2s ease
		}

		.btn-primary {
			background: var(--accent);
			color: #fff
		}

		.btn-primary:hover {
			transform: translateY(-2px);
			filter: saturate(1.05)
		}

		.btn-ghost {
			background: transparent;
			border-color: var(--primary);
			color: var(--primary)
		}

		.btn-ghost:hover {
			background: rgba(0, 87, 183, .06)
		}

		header.nav {
			position: sticky;
			top: 0;
			z-index: 50;
			background: rgba(255, 255, 255, .8);
			backdrop-filter: saturate(180%) blur(8px);
			border-bottom: 1px solid #eee
		}

		.nav__wrap {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: .9rem 0
		}

		.brand {
			display: flex;
			align-items: center;
			gap: .65rem;
			font-family: Poppins, Inter, sans-serif
		}

		.brand__logo {
			width: 38px;
			height: 38px;
			border-radius: 11px;
			background: linear-gradient(135deg, var(--primary), #2b7be8);
			display: grid;
			place-items: center;
			color: #fff;
			box-shadow: var(--shadow)
		}

		.brand__logo svg {
			width: 22px;
			height: 22px
		}

		.brand__name {
			font-weight: 700;
			letter-spacing: .2px
		}

		/* Hero */
		.hero {
			position: relative;
			isolation: isolate
		}

		.hero__bg {
			position: absolute;
			inset: 0;
			background: linear-gradient(180deg, #f7fbff, transparent 60%), radial-gradient(1200px 500px at 80% -10%, #e6f0ff 10%, transparent 60%)
		}

		.hero .container {
			display: grid;
			grid-template-columns: 1.05fr .95fr;
			gap: 2.25rem;
			align-items: center;
			padding: 4rem 0 3rem
		}

		.hero h1 {
			font-family: Poppins, sans-serif;
			font-size: clamp(2rem, 3.2vw + 1rem, 3.25rem);
			line-height: 1.1;
			margin: 0 0 1rem;
			color: #0b1a33
		}

		.hero p {
			font-size: 1.1rem;
			color: #374357;
			margin: 0 0 1.3rem
		}

		.hero .cta {
			display: flex;
			gap: .8rem;
			flex-wrap: wrap
		}

		.hero__card {
			background: #fff;
			border: 1px solid #eef3ff;
			border-radius: 20px;
			padding: 1.2rem;
			box-shadow: var(--shadow)
		}

		.hero__badge {
			display: inline-flex;
			align-items: center;
			gap: .5rem;
			background: #eaf2ff;
			color: #0b387a;
			border-radius: 999px;
			padding: .35rem .7rem;
			font-weight: 600;
			font-size: .86rem;
			margin-bottom: 1rem
		}

		/* Sections */
		section {
			padding: 3.5rem 0
		}

		.section__title {
			font-family: Poppins, sans-serif;
			font-size: clamp(1.6rem, 1.2rem + 1.6vw, 2.2rem);
			margin: 0 0 1rem;
			color: #0b1a33
		}

		.muted {
			color: #5b6678
		}

		/* Benefits */
		.grid-3 {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 1.25rem
		}

		.card {
			background: #fff;
			border: 1px solid #eceff3;
			border-radius: 18px;
			padding: 1.15rem;
			box-shadow: 0 6px 18px rgba(10, 30, 60, .05)
		}

		.icon {
			width: 40px;
			height: 40px;
			border-radius: 12px;
			display: grid;
			place-items: center;
			background: #eef5ff;
			color: var(--primary);
			margin-bottom: .6rem
		}

		/* Steps */
		.steps {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 1rem
		}

		.step {
			background: var(--light);
			border: 1px solid #e9edf3;
			border-radius: 16px;
			padding: 1rem
		}

		.step b {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 28px;
			height: 28px;
			border-radius: 50%;
			background: #fff;
			border: 1px solid #e3e9f5;
			margin-right: .5rem
		}

		/* Features */
		.features {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 1rem
		}

		.feature {
			border: 1px solid #e9edf3;
			border-radius: 16px;
			padding: 1rem;
			background: #fff
		}

		/* Testimonials */
		.testimonials {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 1rem
		}

		.quote {
			background: #fff;
			border: 1px solid #eceff3;
			border-radius: 18px;
			padding: 1.1rem
		}

		.quote p {
			margin: .2rem 0 .6rem
		}

		/* Pricing */
		.pricing {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 1rem
		}

		.plan {
			border: 1px solid #e8ecf3;
			border-radius: 18px;
			background: #fff;
			padding: 1.2rem
		}

		.plan--pro {
			border-color: transparent;
			box-shadow: var(--shadow);
			position: relative
		}

		.badge {
			position: absolute;
			top: -12px;
			right: 14px;
			background: var(--accent);
			color: #fff;
			padding: .3rem .6rem;
			border-radius: 999px;
			font-size: .8rem;
			font-weight: 700
		}

		.price {
			font-family: Poppins, sans-serif;
			font-size: 2rem;
			margin: .3rem 0
		}

		.list {
			margin: .6rem 0 1rem;
			padding: 0;
			list-style: none
		}

		.list li {
			display: flex;
			align-items: flex-start;
			gap: .5rem;
			margin: .35rem 0
		}

		.list svg {
			flex: 0 0 18px
		}

		/* FAQ */
		.faq {
			max-width: 850px
		}

		details {
			background: #fff;
			border: 1px solid #e9edf3;
			border-radius: 14px;
			padding: 1rem;
			margin: .7rem 0
		}

		details summary {
			cursor: pointer;
			font-weight: 600
		}

		/* Footer */
		footer {
			background: #0b1a33;
			color: #c9d6ea;
			padding: 2.2rem 0;
			margin-top: 2rem
		}

		footer a {
			color: #c9d6ea
		}

		/* Responsive */
		@media (max-width: 980px) {
			.hero .container {
				grid-template-columns: 1fr
			}

			.grid-3 {
				grid-template-columns: 1fr 1fr
			}

			.steps {
				grid-template-columns: 1fr 1fr
			}

			.features {
				grid-template-columns: 1fr 1fr
			}

			.pricing {
				grid-template-columns: 1fr
			}

			.testimonials {
				grid-template-columns: 1fr
			}
		}

		@media (max-width: 600px) {

			.grid-3,
			.steps,
			.features {
				grid-template-columns: 1fr
			}

			.hero .container {
				padding: 3rem 0 2rem
			}

			.brand__name {
				display: none
			}
		}
	</style>
</head>

<body>
	<!-- NAVBAR -->
	<header class="nav">
		<div class="container nav__wrap" aria-label="Barra de navegación">
			<a class="brand" href="#" aria-label="Inicio iMenu">
				<span class="brand__logo" aria-hidden="true">
					<!-- Caballo estilo ajedrez minimal -->
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M5 21h14" />
						<path d="M19 21v-6a7 7 0 0 0-7-7h-1l-2-2-3 3 2 2a4 4 0 0 0-2 3v7" />
					</svg>
				</span>
				<span class="brand__name">iMenu</span>
			</a>
			<nav>
				<a href="#funciones" class="btn btn-ghost">Funciones</a>
				<a href="#precios" class="btn btn-ghost">Planes</a>
				<a href="#demo" class="btn btn-primary">Empieza gratis</a>
			</nav>
		</div>
	</header>

	<!-- HERO -->
	<section class="hero" id="inicio">
		<div class="hero__bg" aria-hidden="true"></div>
		<div class="container">
			<div>
				<span class="hero__badge">Hecho en México 🇲🇽</span>
				<h1>Crea tu menú digital en minutos. <br />Sin app, sin complicaciones.</h1>
				<p>Gestiona tu carta, precios e imágenes desde cualquier dispositivo. Tus clientes solo escanean el QR y listo.</p>
				<div class="cta">
					<a href="#demo" class="btn btn-primary">Empieza gratis</a>
					<a href="#como" class="btn btn-ghost">Solicita una demo</a>
				</div>
			</div>
			<div class="hero__card" aria-label="Vista previa del panel iMenu">
				<img src="https://images.unsplash.com/photo-1559339352-11d035aa65de?q=80&w=1400&auto=format&fit=crop" alt="Cliente escaneando un código QR en restaurante" style="border-radius:14px;aspect-ratio:16/10;object-fit:cover;margin-bottom:.8rem" />
				<div style="display:flex;gap:1rem;align-items:center">
					<div class="icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<rect x="3" y="3" width="7" height="7" />
							<rect x="14" y="3" width="7" height="7" />
							<rect x="14" y="14" width="7" height="7" />
							<path d="M3 14h7v7H3z" />
						</svg>
					</div>
					<div>
						<b>QR dinámico</b>
						<p class="muted" style="margin:.2rem 0 0">Comparte tu menú con un solo escaneo — sin descargas.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- BENEFICIOS -->
	<section>
		<div class="container">
			<h2 class="section__title">¿Por qué elegir iMenu?</h2>
			<div class="grid-3" role="list">
				<article class="card" role="listitem">
					<div class="icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M12 8v8M8 12h8" />
							<circle cx="12" cy="12" r="9" />
						</svg>
					</div>
					<h3 style="margin:.2rem 0">Ahorra tiempo</h3>
					<p class="muted">Actualiza tu menú en segundos sin reimprimir ni depender de terceros.</p>
				</article>
				<article class="card" role="listitem">
					<div class="icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<rect x="5" y="2" width="14" height="20" rx="3" />
							<path d="M9 18h6" />
						</svg>
					</div>
					<h3 style="margin:.2rem 0">Sin descargas</h3>
					<p class="muted">Tus clientes solo escanean un código QR. Funciona en cualquier navegador.</p>
				</article>
				<article class="card" role="listitem">
					<div class="icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M3 11l9-8 9 8" />
							<path d="M9 22V12h6v10" />
						</svg>
					</div>
					<h3 style="margin:.2rem 0">Empresa mexicana</h3>
					<p class="muted">Soporte local en español, facturación en MXN y atención cercana.</p>
				</article>
			</div>
		</div>
	</section>

	<!-- CÓMO FUNCIONA -->
	<section id="como" style="background:var(--light)">
		<div class="container">
			<h2 class="section__title">Así de fácil es usar iMenu</h2>
			<div class="steps">
				<div class="step">
					<p><span class="muted"><b>1</b> Crea tu cuenta</span><br />Regístrate en menos de 5 minutos.</p>
				</div>
				<div class="step">
					<p><span class="muted"><b>2</b> Sube tu menú</span><br />Agrega platillos, precios y fotos desde tu panel.</p>
				</div>
				<div class="step">
					<p><span class="muted"><b>3</b> Imprime tu QR</span><br />Colócalo en mesas o comparte tu enlace.</p>
				</div>
				<div class="step">
					<p><span class="muted"><b>4</b> Comparte y recibe</span><br />Tus clientes consultan tu carta en segundos.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- FUNCIONALIDADES -->
	<section id="funciones">
		<div class="container">
			<h2 class="section__title">Herramientas pensadas para tu negocio</h2>
			<div class="features" role="list">
				<div class="feature" role="listitem"><strong>Editor visual de menús</strong>
					<p class="muted">Organiza categorías, fotos y descripciones fácilmente.</p>
				</div>
				<div class="feature" role="listitem"><strong>Control en tiempo real</strong>
					<p class="muted">Activa/desactiva platillos y actualiza precios al instante.</p>
				</div>
				<div class="feature" role="listitem"><strong>QR dinámico y enlaces</strong>
					<p class="muted">Comparte tu menú con un solo link o código QR.</p>
				</div>
				<div class="feature" role="listitem"><strong>Reportes básicos</strong>
					<p class="muted">Vistas, clics y platos más consultados.</p>
				</div>
				<div class="feature" role="listitem"><strong>Múltiples menús</strong>
					<p class="muted">Desayunos, comidas, bebidas y especiales.</p>
				</div>
				<div class="feature" role="listitem"><strong>Soporte en español</strong>
					<p class="muted">Atención cercana por chat y correo.</p>
				</div>
			</div>
			<div style="margin-top:1rem">
				<a href="#demo" class="btn btn-primary">Conoce todas las funciones</a>
			</div>
		</div>
	</section>

	<!-- TESTIMONIOS -->
	<section>
		<div class="container">
			<h2 class="section__title">Restaurantes que ya usan iMenu</h2>
			<div class="testimonials">
				<article class="quote">
					<p>“Con iMenu actualizo mis platillos en segundos y los clientes lo aman.”</p>
					<small class="muted">— Luis García, Café Aroma (CDMX)</small>
				</article>
				<article class="quote">
					<p>“Ahorro en impresiones y tengo el control total de mi menú.”</p>
					<small class="muted">— Sandra Pérez, Restaurante La Terraza</small>
				</article>
			</div>
		</div>
	</section>

	<!-- PRECIOS -->
	<section id="precios" style="background:var(--light)">
		<div class="container">
			<h2 class="section__title">Planes flexibles para cada negocio</h2>
			<div class="pricing">
				<div class="plan">
					<h3>Gratis</h3>
					<p class="muted">Pruebas o food trucks</p>
					<p class="price">$0 <small class="muted">/ mes</small></p>
					<ul class="list">
						<li>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20 6L9 17l-5-5" />
							</svg>
							1 menú y QR único
						</li>
						<li>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20 6L9 17l-5-5" />
							</svg>
							Editor visual básico
						</li>
					</ul>
					<a href="#demo" class="btn btn-ghost">Probar</a>
				</div>
				<div class="plan plan--pro">
					<span class="badge">Más popular</span>
					<h3>Pro</h3>
					<p class="muted">Restaurantes y cafés</p>
					<p class="price">$149 <small class="muted">/ mes</small></p>
					<ul class="list">
						<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20 6L9 17l-5-5" />
							</svg> Hasta 5 menús</li>
						<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20 6L9 17l-5-5" />
							</svg> Personalización visual</li>
						<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20 6L9 17l-5-5" />
							</svg> Reportes básicos</li>
						<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20 6L9 17l-5-5" />
							</svg> Soporte prioritario</li>
					</ul>
					<a href="#demo" class="btn btn-primary">Empezar</a>
				</div>
				<div class="plan">
					<h3>Empresarial</h3>
					<p class="muted">Cadenas o franquicias</p>
					<p class="price">Desde $499 <small class="muted">/ mes</small></p>
					<ul class="list">
						<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20 6L9 17l-5-5" />
							</svg> Múltiples sucursales</li>
						<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20 6L9 17l-5-5" />
							</svg> Roles y permisos</li>
						<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20 6L9 17l-5-5" />
							</svg> Integraciones a medida</li>
					</ul>
					<a href="#demo" class="btn btn-ghost">Hablar con ventas</a>
				</div>
			</div>
		</div>
	</section>

	<!-- FAQ -->
	<section>
		<div class="container faq">
			<h2 class="section__title">¿Tienes dudas? Aquí te respondemos.</h2>
			<details>
				<summary>¿Necesito una app para usar iMenu?</summary>
				<p class="muted">No. Tus clientes escanean el QR con su cámara o abren el enlace en el navegador.</p>
			</details>
			<details>
				<summary>¿Puedo actualizar precios desde el celular?</summary>
				<p class="muted">Sí. El panel de control es responsive y funciona en móviles, tablets y computadoras.</p>
			</details>
			<details>
				<summary>¿Ofrecen soporte en español?</summary>
				<p class="muted">Claro. Somos una empresa mexicana con atención por chat y correo.</p>
			</details>
		</div>
	</section>

	<!-- FOOTER -->
	<footer>
		<div class="container" style="display:grid;gap:1rem">
			<div style="display:flex;align-items:center;gap:.6rem">
				<span class="brand__logo" aria-hidden="true" style="background:linear-gradient(135deg,#0b2e6e,#184b9d)">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M5 21h14" />
						<path d="M19 21v-6a7 7 0 0 0-7-7h-1l-2-2-3 3 2 2a4 4 0 0 0-2 3v7" />
					</svg>
				</span>
				<strong>iMenu</strong>
			</div>
			<div style="display:flex;gap:1rem;flex-wrap:wrap">
				<a href="#terminos">Términos y condiciones</a>
				<a href="#privacidad">Política de privacidad</a>
			</div>
			<small>iMenu © 2025 — Desarrollado en México 🇲🇽 · contacto@imenu.mx · 777‑163‑9113</small>
		</div>
	</footer>

	<!-- Accesibilidad: saltos a secciones con teclado -->
	<a href="#inicio" style="position:fixed;right:14px;bottom:14px;background:#fff;border:1px solid #e6ecf7;padding:.6rem .8rem;border-radius:999px;box-shadow:var(--shadow);">↑</a>
</body>

</html>
