<template>
	<div class="spinner" :class="{inline: inline, main : !inline}"><i class="fa fa-spin fa-spinner fa-4x"></i></div>
</template>

<script type="text/javascript">
	export default {
		name: "Spinner",
		props: ["inline"]
	}
</script>

<style type="text/css" scoped>
	.spinner {
		
		
		text-align: center;
		height: 90px;
		width: 90px;
		padding: 10px;
		padding-top: 17px;
		color: white;
		background: rgba(10,10,10,0.5);
		border-radius: 8px;
		
	}

	.spinner.main {
		position: fixed;
		top: calc(50% - 40px);
		bottom: 0;
		left: calc(50% - 40px);
		right: 0;
		z-index: 1000;
		box-shadow: 0 0 10px black;
	}

	.spinner.inline {
		position: static;
		height: 45px;
		width: 45px;
		padding: 5px;
		padding-top: 7px;
		font-size: 8px;
		display: block;
		margin: auto;
		box-shadow: 0 0 5px black;
	}
</style>