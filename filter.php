<?php
/**
 * Filter to replace youku links with embed code
 *
 * @package    filter
 * @subpackage youku
 * @author     Shane Elliott, Pukunui (http://pukunui.com)
 * @copyright  2012 Beijing Open University, China
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_youku extends moodle_text_filter {

     /**
      * @var array global configuration for this filter
      *
      * This might be eventually moved into parent class if we found it
      * useful for other filters, too.
      */
    protected static $globalconfig;

    /**
     * Apply the filter to the text
     *
     * @see filter_manager::apply_filter_chain()
     * @param string $text to be processed by the text
     * @param array $options filter options
     * @return string text after processing
     */
    public function filter($text, array $options = array()) {

        // TODO: Remove any script and other tags which we do not wish to filter. It
        // is unlikely that we'll find any suitable links within these areas so for
        // now this part has been left unfinished.

        // We should search only for links to youku
        $search = "{<a.*?href=\".*?\.youku\.com\/.*?id_([[:alnum:]]*)\.html.*?[\?]?(d=[[:digit:]]+x[[:digit:]]+)?.*?<\/a>}";
        $text = preg_replace_callback($search, array($this, 'callback'), $text);

        return $text;
    }

    ////////////////////////////////////////////////////////////////////////////
    // internal implementation starts here
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Returns the global filter setting
     *
     * If the $name is provided, returns single value. Otherwise returns all
     * global settings in object. Returns null if the named setting is not
     * found.
     *
     * @param mixed $name optional config variable name, defaults to null for all
     * @return string|object|null
     */
    protected function get_global_config($name=null) {
        $this->load_global_config();
        if (is_null($name)) {
            return self::$globalconfig;

        } elseif (array_key_exists($name, self::$globalconfig)) {
            return self::$globalconfig->{$name};

        } else {
            return null;
        }
    }

    /**
     * Makes sure that the global config is loaded in $this->globalconfig
     *
     * @return void
     */
    protected function load_global_config() {
        if (is_null(self::$globalconfig)) {
            self::$globalconfig = get_config('filter_emoticon');
        }
    }

    /**
     * Replace youku links with embed code
     *
     * @param array $matches  results from preg_match
     * @return void
     */
    private function callback(array $matches) {
        // Set some defaults for the dimensions
        $w=400;
        $h=400;

        // Did any dimensions get passed as part of the URL?
        if (!empty($matches[2])) {
            preg_match("/d=([0-9]{1,3})x([0-9]{1,3})/", $matches[2], $dimensions);
            if (count($dimensions) == 3) {
                $w = $dimensions[1];
                $h = $dimensions[2];
            }
        }

        /// We use the object tag rather than the embed tag as it provides
        /// better cross browser compatibility and html compliance
        $embed = '<object
                    type="application/x-shockwave-flash"
                    data="http://player.youku.com/player.php/sid/'.$matches[1].'/v.swf"
                    width="'.$w.'"
                    height="'.$h.'"
                    <param name="movie" value="http://player.youku.com/player.php/sid/'.$matches[1].'/v.swf">
                  </object>';

        return $embed;
    }
}
