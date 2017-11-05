<template>
	<header>
		<div class="page-heading" :class="{ menuShowing: menuShowing }">
			<div class="menu-button" v-on:click="toggleMenu"><i class="fa fa-navicon"></i></div>
			<div class="side-menu-button menu-button" v-on:click="toggleMenu"><i class="fa fa-navicon"></i></div>
			<h1>
				<i class='fa' :class="$route.meta.icon"></i> 
				{{ pageTitle.mainTitle }} 
				<small v-if="pageTitle.subTitle">{{ pageTitle.subTitle }}</small>
			</h1>

			<ul class="menu-list">

				<li v-for="route in mutatedRoutes" 
					v-if="route.showRoute && route.meta.navGroup.indexOf('top') >= 0">
					<router-link :to="route.path">
						<i class="fa fa-fw" :class="route.meta.icon"></i>
						<div>{{ route.meta.titleText }}</div>
					</router-link>
				</li>

				<!--<li>
					<a href="#" v-on:mouseover="settingsMenuVisible = true" v-on:mouseleave="settingsMenuVisible = false">
						<i class="fa fa-gear"></i>
						<div>Settings</div>
					</a>

					<ul v-if="settingsMenuVisible" class="menu-list-dropdown">

						<li v-for="route in mutatedRoutes" 
							v-if="route.showRoute && route.meta.navGroup == 'app'">
							<router-link :to="route.path">
								<i class="fa fa-fw" :class="route.meta.icon"></i>
								{{ route.meta.titleText }}
							</router-link>
						</li>
					</ul>
				</li>-->
			</ul>


		</div>

		<nav class="menu" :class="{ menuShowing: menuShowing }">
			
			<div class="menu-block">
				<h2 class="menu-heading">		
					Manage 
				</h2>

				<!--<div v-if="updateAvailable" v-on:click="installUpdate" class="alert alert-info update-alert" style="text-align: center;margin-top: 10px;padding: 3px;">
					<i class="fa fa-refresh"></i>
					Update Available<br>
					<small>Click to install update</small>
				</div>-->

				<ul class="menu-list">

					<li v-for="route in mutatedRoutes" 
						v-if="route.showRoute && route.meta.navGroup.indexOf('main') >= 0">
						<router-link :to="route.path">
							<i class="fa fa-fw" :class="route.meta.icon"></i>
							{{ route.meta.titleText }}
						</router-link>
					</li>
				</ul>
			</div>

			<div class="menu-block">
				<h2 class="menu-heading">		
					Settings 
				</h2>

				<ul class="menu-list">

					<li v-for="route in mutatedRoutes" 
						v-if="route.showRoute && route.meta.navGroup.indexOf('app') >= 0">
						<router-link :to="route.path">
							<i class="fa fa-fw" :class="route.meta.icon"></i>
							{{ route.meta.titleText }}
						</router-link>
					</li>
				</ul>
			</div>

			<!--<p style="text-align: center;"><small>Version #{{appVersion}}</small></p>-->
		</nav>
	</header>
</template>

<script type="text/javascript">

	export default {
		name: 'DashboardHeader',
		props: ['menuShowing','spinnerVisible',"isOffline","updateAvailable","pageTitle"],
		data () {
			return {
				userType: localStorage.userType,
				appVersion: localStorage.appVersion,
				settingsMenuVisible: false
			}
		},
		computed: {
			localBankAccounts () {
				let localBankAccounts = []
				let vm = this

				this.bankAccounts.forEach(function(account, index) {

					if (!("editButtonVisible" in account)) {
						vm.$set(account,'editButtonVisible',false)
					}

					localBankAccounts.push(account)
				});

				return localBankAccounts
			},
			mutatedRoutes() {
				let routes = []

				let vm = this

				this.$root.$data.routes.forEach(function(route, index){
					let showRouteInMenu = false

					//route must be listed as navbar = true
					if ("meta" in route && "navbar" in route.meta && route.meta.navbar) {

						//app must either be offline, or have offline access for this route enabled
						if (!vm.isOffline || vm.isOffline && 'offline' in route.meta && route.meta.offline) {
							showRouteInMenu = true
						}

						//if perm property exists in route, array must contain the user type
						if ("perm" in route.meta) {
							showRouteInMenu = false

							if (route.meta.perm.indexOf(localStorage.userType) >= 0)
								showRouteInMenu = true
						}
						
					}

					route['showRoute'] = showRouteInMenu

					routes.push(route);

				})

				return routes
			}
		},

		methods: {
			toggleMenu: function(e) {
				this.$emit("toggleMenu");
			},
			newAudit() {
				this.$emit("newAudit");
			},
			installUpdate() {
				let vm = this

				//request the server generate a new app cache manifest
				this.getJSON(window.apiBase+"tools/update-app").then(function(response){
					localStorage.appVersion = response.version
					location.reload()
				})

			},
			logout () {
				localStorage.removeItem("apiKey");
			}
		}
		
	}
</script>

<style type="text/css" >
	header {
		font-family: 'Roboto', sans-serif;

	}

	.section-wrapper.menuShowing {
		margin-left: 250px;
	}

	.page-heading {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		background-color: rgba(20,15,10,1);
		background-color: #003540;
		color: #fff;
		padding: 5px;
		transition: left 0.5s;
		min-width: 320px;
		z-index: 11;
		min-height: 50px;
	}

	.page-heading.menuShowing {
		left: 250px;
		
	}

	.page-heading h1 {
		display: inline-block;
		vertical-align: top;
		margin: 0;
		margin-top: 8px;
		margin-left: 20px;
		font-size: 22px;

	}

	.page-heading h1 small {
		color: #9a9a9a
	}

	.page-heading .menu-list {
		float: right;
		color: #e8e8e8;
		margin: -5px;
	}

	.page-heading .menu-list li {
		display: inline-block;
		text-align: center;
		
	}

	.page-heading .menu-list li i {
		font-size: 20px;
	}

	.page-heading .menu-list li a {
		font-size: 12px;
		color: inherit;
		padding: 5px;
		display: block;
	}

	.page-heading .menu-list li:hover a, .page-heading .menu-list .router-link-exact-active {
		text-decoration: none;
		cursor: pointer;
		color: #fff;
		background-color: #006477;
	}

	.new-transaction-button-container {
		position: absolute;
		top: 13px;
		right: 15px;
	}

	.header-flag {
		position: absolute;
		top: 5px;
		right: 45px;
		padding: 5px;
		border-radius: 4px;
	}

	.offline-flag {
		background-color: #ff4747;
		color: white;
	}

	.transactions-synced-flag {
		background-color: #4CB050;
		color: white;
	}

	.update-alert:hover {
		cursor: pointer;
		box-shadow: 0 0 5px white;
	}

	.menu {
		width: 250px;
		padding: 0;
		padding-top: 0;
		background-color: rgba(20,15,10,1);
		background-color: #003540;
		color: #fff;
		position: fixed;
		top: 0;
		left: -250px;
		bottom: 0;
		overflow-y: auto;
		transition: left 0.5s;
		z-index: 10;
	}

	.menu-block {
		border-left: 3px solid #2fff96;
		padding: 10px;
		padding-top: 0;
		padding-bottom: 0;
	}

	.menu-button-heading {
		cursor: pointer;
		background-color: #03A9F4;
		padding: 12.5px;
		text-decoration: none;
		margin-left: -10px;
		margin-right: -10px;
		margin-top: 0;
	}

	.menu-button-heading:hover {
		opacity: 0.8
	}

	.menu.menuShowing {
		left: 0;
	}

	.menu-list {
		list-style: none;
		margin: 0;
		padding: 0;
	}

	.navbar-logo {
		background-color: white;
		margin-left: -10px;
		margin-right: -10px;
		width: calc(50% + 20px);
	}

	header nav h2 {
		font-size: 12pt;
		text-transform: uppercase;
		margin-top: 20px;
		text-decoration: underline;
		color: inherit;
	}

	header nav h3 {
		font-size: 11pt;
		text-transform: uppercase;
		margin-top: 20px;
	}

	.menu li a {
		color: #e8e8e8;
		display: block;
		padding: 5px;
		text-decoration: none;
		font-size: 18px;
		transition: transform 0.2s;
		font-weight: lighter;
    	font-size: 12pt;
    	text-transform: uppercase;
	}

	.menu li a:hover {
		text-decoration: none;
		color: white;
		background-color: rgba(255,255,255,0.1);
		transform: scale(1.05, 1.05);
	}

	.menu-button {
		font-size: 32px;
		cursor: pointer;
		display: inline-block;
		line-height: 0;
		margin-top: 4px;
	}

	.menu .menu-button {
		float: right;
	}

	header .icon-button.fa-plus {
		color: #fff;
		margin-top: -3px;
	}

	header .text-danger {
		color: #fd5757;
	}

	header .text-success {
		color: #60ef62;
	}

	.main-alert {
		position: fixed;
		top: 5px;
		right: 5px;
		z-index: 50
	}

	.side-menu-button {
		display: none;
	}

	.panel {
		border-radius: 2px;
	}

	@media (max-width: 1050px) {
		.section-wrapper.menuShowing {
			margin-left: 0;
		}
	}

	@media (max-width: 800px) {
		.page-heading .menu-list {
			display: none;
		}
	}

	@media (max-width: 450px) {


		.header-flag span {
			display: none;
		}

		.side-menu-button {
			display: block;
			opacity: 0;
			position: fixed;
			top: calc(50% - 20px);
			right: 0;
			z-index: 10;
			color: #003540;
		}
	}
</style>