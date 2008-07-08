/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.7
 * @package     Haefko
 */



/**
 * JS funkce pro vytvoreni/zobrazeni chybove zpravy
 * @param   string  id tagu
 * @param   string  chybová zpráva
 * @return  void
 */
function HFcreateErrorLabel(name, message)
{
    if ($('#' + name + ' ~ label[generated=true]').length) {
        $('#' + name + ' ~ label[generated=true]').show().html(message);
    } else {
        $('#' + name + '').parent().append('<label generated="true" class="error" for="' + name + '">' + message + '</label>');
    }
}



/**
 * JS funkce pro validaci
 * @param   string  pravidlo
 * @param   mixed   hodnota
 * @param   mixed   argument
 * @return  bool
 */
function HFisValid(rule, value, arg)
{
    switch (rule) {
        case 'expression':  return value;
        case 'equal':       return value == arg;
        case 'filled':      return value != arg && value.length > 0;
        case 'notfilled':   return value == arg || value.length == 0;
        case 'numeric':     return value.match(/^(-)?(\d*)(\.?)(\d*)$/);
        case 'email':       return value.match(/^[^@]+@[^@]+\.[a-z]{2,6}$/i);
        case 'url':         return value.match(/^.+\.[a-z]{2,6}(\\\/.*)?$/i);
        case 'minlength':   return value.length >= parseInt(arg);
        case 'maxlength':   return value.length <= parseInt(arg);
        case 'length':      return value.length == parseInt(arg);
        case 'inarray':     return jQuery.inArray(value, arg) >= 0;
        case 'notinarray':  return jQuery.inArray(value, arg) == -1;
        default:            return value.match(arg);
    }
}