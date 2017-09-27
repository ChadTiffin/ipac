<template>
	<div :class="classes">
		<ul class="tab-buttons"  v-if="$route.params.id != 'new'">
			<slot></slot>
		</ul>
		<div style="clear:both"></div>
	</div>

</template>

<script type="text/javascript">
	export default {
		name: "NavTabs",
		props: ["classes"]
	}
</script>

<style type="text/css">
	.tab-buttons {
		list-style: none;
		margin: 0;
		margin-bottom: -5px;
		margin-top: -3px;
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
	}

	.tab-buttons li.active a {
		border-left: 3px solid #003540;
	}

	.tab-buttons li.active a {
		background-color: white;
	}
</style>