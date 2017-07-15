import Homepage from '../Components/Homepage.jsx';
import Archive from '../Components/Archive.jsx';
import Single from '../Components/Single.jsx';

jQuery(window).load(function() {
	jQuery('#wp-social-login').appendTo('#userbox-links');
	jQuery('#wp-social-login').removeClass('hidden');
});