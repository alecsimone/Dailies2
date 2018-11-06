<?php /* Template Name: User Management */
get_header();



?>


<?php if (currentUserIsAdmin()) { ?>
	<section id="userManagementApp"></section>
<?php } else { ?>
	<section>You aren't supposed to be here.</section>
<?php } ?>

<?php get_footer(); ?>

<style>
	body {
		color: #121212;
	}
	body:before {
		background: none;
	}
	form {
	    padding: 8px;
	    border: 1px solid hsla(0, 0%, 0%, .3);
	    display: inline-block;
	    margin: 6px 12px;
	    border-radius: 3px;
	}
	.linkerInputWrapper, .userInputWrapper {
		display: inline-block;
		margin-right: 12px;
	}
	#userLinker label, #addRep label {
		margin-right: 3px;
	}
	#userLinkerSubmitButton, #addRepSubmitButton {
		border: 1px solid #121212;
		padding: 3px 6px;
		color: #121212;
		font-size: 14px;
	}
	table#UserManagement {
		margin: auto;
		border-collapse: collapse;
	}
	th {
		padding: 0;
	}
	th.headerSortUp, th.headerSortDown {
		background: hsla(210, 80%, 80%, .2);
	}
	td {
		border: 1px solid #121212;
	}
	td.dailiesIDColumn, th.dailiesIDColumn, td.picColumn {
		max-width: 50px;
		overflow-wrap: break-word;
	}
	td.emailColumn, td.dailiesNameColumn {
		max-width: 200px;
		overflow-wrap: break-word;
	}
	table img {
		vertical-align: middle;
	}
	img.userManagementPic {
		width: 24px;
		max-width: 30px;
		font-size: 8px;
		height: 24px;
		padding: 3px;
	}
	img.trash {
		width: 18px;
		height: 18px;
		padding: 6px;
	}
	select {
	    border: none;
	    font-family: inherit;
	    font-size: inherit;
	}
</style>