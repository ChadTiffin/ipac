<template>
	<div :class="classes">
		<ul class="tab-buttons"  v-if="$route.params.id != 'new'">
			<slot></slot>

			<li v-for="tab in tabs" :class="{active: $route.params.tab == tab.path}">
				<router-link :to="tab.path">
					<i class="fa" :class="tab.icon"></i>
					{{ tab.label }}
					<span v-if="tab.bubble > 0" class="notification-bubble">{{ tab.bubble }}</span>
				</router-link>
			</li>
		</ul>
		<div style="clear:both"></div>
	</div>

</template>

<script type="text/javascript">
	export default {
		name: "NavTabs",
		props: ["classes","tabs"]
	}
</script>

<style type="text/css">
	.tab-buttons {
		list-style: none;
		margin: 0;
		margin-bottom: -5px;
		margin-top: -3px;
		padding: 0;
	}

	.tab-buttons li {
		display: inline-block;
	}

	.tab-buttons li a {
		display: block;
		padding: 15px;
		padding-top: 10px;
		padding-bottom: 11px;
		text-decoration: none;
	}

	.tab-buttons li a:hover {
		text-decoration: none;
		background-color: white;
		color: #004E5C
	}

	.tab-buttons li.active a {
		border-left: 3px solid #003540;
		border-left: none;

		background-color: #9eedfd;
		background-color: white;
		color: #004E5C
	}

	.notification-bubble {
		border-radius: 50%;
		background-color: Red;
		color: white;
		padding: 3px;
		font-size: 8pt;
		height: 18px;
		width: 18px;
		display: inline-block;
		text-align: center;
		padding-top: 2px;
		position: absolute;
		top: 0;
		right: 0;
		box-shadow: 0.5px 0.5px 2px #404040;
		font-weight: bold;
	}

	.button-bar .tab-buttons li {
		position: relative;
	}

	@media (max-width: 700px) {
		.tab-buttons li {
			width: calc(50% - 2px)
		}

		.tab-buttons {
			margin-top: 10px;
		}
	}
</style>