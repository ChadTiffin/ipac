<template>
	<header>
		<div class="page-heading" :class="{ menuShowing: menuShowing }">
			<div class="menu-button" v-on:click="toggleMenu"><i class="fa fa-navicon"></i></div>
			<div class="side-menu-button menu-button" v-on:click="toggleMenu"><i class="fa fa-navicon"></i></div>
			<h1><i class='fa' :class="$route.meta.icon"></i> {{ $route.meta.titleText }}</h1>
		</div>

		<nav class="menu" :class="{ menuShowing: menuShowing }">
			<h2 
				class="menu-heading">		
				Menu 
			</h2>
			<ul class="menu-list">

				<li v-for="route in $root.$data.routes" v-if="('meta' in route) && route.meta.navbar">
					<router-link :to="route.path">
						<i class="fa fa-fw" :class="route.meta.icon"></i>
						{{ route.meta.titleText }}
					</router-link>
				</li>
			</ul>
		</nav>
	</header>
</template>

<script type="text/javascript">

	export default {
		name: 'DashboardHeader',
		props: ['menuShowing','spinnerVisible'],
		data () {
			return {
				userType: localStorage.userType
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
			}
		},
		methods: {
			toggleMenu: function(e) {
				this.$emit("toggleMenu");
			},
			showModal: function(e) {
				this.$emit("showModal");
			},
			showImportModal () {
				this.$emit("showImportModal")
			},
			showAccountsModal (account) {
				this.$emit("showAccountsModal",account)
			},
			navigate(location) {
				this.$emit("navigate",location)
			},
			logout () {
				localStorage.removeItem("apiKey");
			}
		}
	}
</script>

<style type="text/css" >
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
	}

	.page-heading.menuShowing {
		left: 250px;
		
	}

	.page-heading h1 {
		display: inline-block;
		vertical-align: top;
		margin: 0;
		margin-top: 5px;
		margin-left: 20px;
		font-size: 22px;

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

	.menu {
		width: 250px;
		padding: 10px;
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

	header nav h2 {
		font-size: 12pt;
		text-transform: uppercase;
		margin-top: 20px;
		text-decoration: underline;
	}

	header nav h3 {
		font-size: 11pt;
		text-transform: uppercase;
		margin-top: 20px;
	}

	.menu li a {
		color: #fff;
		display: block;
		padding: 5px;
		text-decoration: none;
		font-size: 18px;
		transition: transform 0.2s;
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
	}

	.menu .menu-button {
		float: right;
	}

	.bank-accounts {
		list-style: none;
		padding: 0;
	}

	.bank-balance {
		text-align: right;
		float: right;
	}

	.bank-accounts li {
		padding-top: 2px;
		padding-bottom: 2px;
		transition: transform 0.2s;
	}

	.bank-accounts .account:hover {
		background-color: rgba(255,255,255,0.1);
		transform: scale(1.03, 1.03);
		cursor: pointer;
	}

	.bank-accounts .total {
		border-top: 1px solid #a0a0a0;
		margin-top: 5px;
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

	@media (max-width: 1050px) {
		.section-wrapper.menuShowing {
			margin-left: 0;
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