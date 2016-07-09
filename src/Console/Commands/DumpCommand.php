<?php namespace Aktiweb\Translation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DumpCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translation:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will dump database translations into classic language files.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $query = \DB::connection(\Config::get('translation-db.database'))->table('translations')->select('locale', 'group', 'name', 'value');
        $this->addOptionToQuery($query, 'locale');
        $this->addOptionToQuery($query, 'group');
        $results = $query->get();

        // Reorder the data
        $dump = [];
        foreach ($results as $result) {
            $dump[$result->locale][$result->group][$result->name] = $result->value;
        }
        $this->write($dump);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['locale', 'l', InputOption::VALUE_OPTIONAL, 'Specify a locale.', null],
            ['group', 'g', InputOption::VALUE_OPTIONAL, 'Specify a group.', null],
        ];
    }

    /**
     * @param Builder $query
     * @param string $option
     */
    protected function addOptionToQuery(Builder $query, $option)
    {
        if ($this->option($option) !== null) {
            $query->where($option, $this->option($option));
        }
    }

    protected function write($dump)
    {
        $lang_path = base_path() . '/resources/lang';
        $date = date_create()->format('Y-m-d H:i:s');
        foreach ($dump as $locale => $groups) {
            foreach ($groups as $group => $content) {
                $path = $lang_path . "/{$locale}";
                if (!\File::exists($path)) {
                    \File::makeDirectory($path, 0755, true);
                }

                $file = $path . "/{$group}.php";
                $content = $this->fixNulledValues($content);
                $data = $this->getFileTemplate($content, $locale, $group, $date);

                // Display the results
                if (\File::put($file, $data)) {
                    $this->info("Dumped: {$file}");
                }
                else {
                    $this->error("Failed to dump: {$file}");
                }
            }
        }
    }

    private function assignArrayByPath(&$arr, $path, $value)
    {
        $keys = explode('.', $path);

        while ($key = array_shift($keys)) {
            $arr = &$arr[$key];
            if (is_array($arr)) ksort($arr);
        }

        $arr = $value;
    }

    /**
     * @param $content
     * @param $locale
     * @param $group
     * @return string
     */
    protected function getFileTemplate($content, $locale, $group, $date)
    {
        $content = $this->convertToOrderedNestedArray($content);

        $array_text = var_export($content, true);
        $array_text = $this->getArrayText($content);


        $data = <<<EOF
<?php
return {$array_text};
EOF;
        return $data;
    }

    private function getArrayText($arr, $ident_size = 4, $level = 0)
    {
        $ident = str_repeat(' ', $ident_size * $level);
        $result = "array(\n";

        $max_key_length = $this->getMaxKeyLength($arr);

        foreach ($arr as $key => $value) {

            $filler = str_repeat(' ', $max_key_length - strlen($key));

            if (is_array($value)) {
                $result .= $ident . str_repeat(' ', $ident_size) . "'{$key}' => ";
                $result .= $this->getArrayText($value, $ident_size, $level + 1);
            }
            else {
                $result .= $ident . str_repeat(' ', $ident_size) . "'{$key}'{$filler} => ";
                $result .= var_export($value, true);
            }
            $result .= ",\n";
        }
        $result .= $ident . ")";

        //if($level > 0) $result .= ",\n";

        return $result;
    }

    /**
     * @param $content
     * @return mixed
     */
    protected function fixNulledValues($content)
    {
        foreach ($content as $key => $value) {
            if ($value === null) {
                $content[$key] = $key;
            }
        }
        return $content;
    }

    /**
     * @param $content
     * @return array
     */
    private function convertToOrderedNestedArray($content)
    {
        $new_content = array();
        foreach ($content as $key => $value) {
            $this->assignArrayByPath($new_content, $key, $value);
        }
        $content = $new_content;
        return $content;
    }

    /**
     * @param $arr
     * @return int
     */
    private function getMaxKeyLength($arr)
    {
        $keys = array_keys($arr);
        usort($keys, function ($a, $b) {
            return strlen($a) < strlen($b);
        });
        $max_key_length = strlen(array_shift($keys));
        return $max_key_length;
    }

}
