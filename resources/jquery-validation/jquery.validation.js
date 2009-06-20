/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Libs
 */


$.fn.validate = function(rules, conditions) {
	function isValid(rule, val, arg) {
		if (arg != null && arg['control'] != undefined)
			arg = ($('#' + arg['control'])).val();

		switch (rule) {
			case 'equal': return val == arg;
			case 'filled': return val != '' && val != "";
			case 'integer': return /^\d+$/.test(val);
			case 'float': return /^\d+(\.\d+)?$/.test(val);
			case 'length':
				val = val.length;
			case 'range':
				if (val == "")
					return false;

				if (arg[0] != undefined && arg[1] != undefined)
					return (val >= arg[0] && val <= arg[1]);
				else
					return arg == val;
			case 'url': return /^.+\.[a-z]{2,6}(\\\/.*)?$/i.test(val);
			case 'email': return /^[^@\s]+\@[^@\s]+\.[a-z]{2,10}$/i.test(val);
			case 'callback': return true;
			case 'regexp': return val.match(arg);
			default: return true;
		}
	}

	function showError(control, message) {
		message = '<label for="' + control + '">' + message + '</label>';
		$('#' + control + '-error').html(message);
	}

	function removeError(control) {
		$('#' + control + '-error').html('');
	}

	function getValue(control, def) {
		value = $('#' + control).val();

		if ($('#' + control).is('input[type=text]'))
			value.replace(/^\s+|\s+$/g, '');

		if (value == def)
			value = '';

		return value;
	}

	this.submit(function() {
		has = [];
		ret = true;

		// conditions
		for (y in conditions) {
			cond = conditions[y];
			valid = isValid(cond['rule'], getValue(cond['control'], cond['default']), cond['arg']);
			valid = cond['negative'] ? !valid : valid;
			if (valid)
				rules.contat(condition['rules']);
		}

		// rules
		for (i in rules) {
			row = rules[i];
			if (has[row['control']] == true)
				continue;

			removeError(row['control']);
			valid = isValid(row['rule'], getValue(row['control'], row['default']), row['arg']);
			valid = row['negative'] ? !valid : valid;
			if (!valid) {
				has[row['control']] = true;
				showError(row['control'], row['message']);
				if (ret == true)
					$('#' + row['control']).focus();

				ret = false;
			}
		}

		return ret;
	});

	return this;
}