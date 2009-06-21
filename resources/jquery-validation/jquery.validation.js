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
	function isValid(rule, val, arg, row) {
		if (arg != null && arg['control'] != undefined)
			arg = getValue(arg['control']);

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
			case 'callback':
				if (arg['url'] != null) {
					data = {"value": val, "arg": arg['arg']};
					$.post(arg['url'], data, function(response) {
						if (!response || (!response['valid'] != undefined && response['valid'] == false)) {
							if (response['message'] != undefined)
								showError(row['control'], response['message']);
							else
								showError(row['control'], row['message']);
						}
					}, "json");
				} else {
					return true;
				}
			case 'regexp': return val.match(arg);
			default: return true;
		}
	}

	function showError(control, message) {
		message = '<label for="' + formName + control + '">' + message + '</label>';
		if ($('#' + formName + control + '-error').length > 0)
			$('#' + formName + control + '-error').html(message);
		else
			$('#' + formName + control).after('<div id="' + formName + control + '-error">' + message + '</div>');
	}

	function removeError(control) {
		$('#' + formName + control + '-error').html('');
	}

	function getValue(control, def) {
		input = $('#' + formName + control);
		if (input.is('div.multi-inputs'))
			value = $('input[name=' + realFormName + '\[' + control + '\]]:checked').val();
		else if (input.is('input[type=checkbox]'))
			value = input.is(':checked');
		else
			value = input.val();
 
		if (input.is('input[type=text]'))
			value.trim();
		if (value == def)
			value = '';

		return value;
	}

	realFormName = $(this).attr('id');
	formName = realFormName + '-';
	this.submit(function() {
		has = [];
		ret = true;

		// conditions
		for (y in conditions) {
			cond = conditions[y];
			valid = isValid(cond['rule'], getValue(cond['control'], cond['default']), cond['arg']);
			valid = cond['negative'] ? !valid : valid;
			if (valid)
				rules = rules.concat(cond['rules']);
		}

		// rules
		for (i in rules) {
			row = rules[i];
			if (has[row['control']] == true)
				continue;

			removeError(row['control']);
			valid = isValid(row['rule'], getValue(row['control'], row['default']), row['arg'], row);
			if (valid == null)
				continue;

			valid = row['negative'] ? !valid : valid;
			if (!valid) {
				has[row['control']] = true;
				// dynamic error message
				if (row['rule'] == 'equal' && row['arg'] != undefined && row['arg']['control'] != undefined)
					row['message'] = row['message'].replace(/\%s/, $('#' + formName + row['arg']['control']).val());

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