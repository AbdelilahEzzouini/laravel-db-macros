<?php

namespace AbdelilahEzzouini\DbMacros;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class DbMacrosServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /**
         * Select a binding from the database.
         *
         * @param string $query SQL query with named parameters (e.g., ":param" or "[: param_array]")
         * @param array $bindings Associative array of parameter values (e.g., ['param' => 'value', 'param_array' => [1, 2, 3]])
         * @param string $statementType Type of SQL statement to execute (default: 'select')
         *                              Other valid types: 'insert', 'update', 'delete', 'statement', 'affectingStatement' based on the Eloquent method used
         *                              Use 'affectingStatement' for INSERT/UPDATE/DELETE operations that return affected row count
         * @return array Results of the query
         *
         * Example usage:
         * DB::binding('SELECT * FROM table WHERE id = :id AND status = :status', [
         *     'id' => 1,
         *     'status' => 'active'
         * ]);
         *
         * For array parameters:
         * DB::binding('SELECT * FROM table WHERE id IN [: ids]', [
         *     'ids' => [1, 2, 3]
         * ]);
         */
        DB::macro('binding', function (string $query, array $bindings = [], string $statementType = 'select'): mixed
        {
            // Define the extractPattern function within the macro
            $extractPattern = function ($string) {
                $pattern = '/(?::(\w+)|\[\s*:\s*(\w+)\s*\])/';
                if (preg_match($pattern, $string, $match)) {
                    if (isset($match[1]) && !empty($match[1])) {
                        return $match[1];
                    } elseif (isset($match[2]) && !empty($match[2])) {
                        return $match[2];
                    }
                }
                return null;
            };
            // Define the convertNamedToPositionalParams function within the macro
            $convertNamedToPositionalParams = function ($query, $params) use ($extractPattern) {
                // Find all named parameters in the query
                preg_match_all('/\[?:([a-zA-Z0-9_]+)\]?/', $query, $matches);
                // Get the unique named parameters
                $namedParams = $matches[0];
                // Create an empty array for the positional parameters
                $positionalParams = [];
                // Replace named parameters with positional placeholders
                foreach ($namedParams as $namedParam) {
                    // Remove the colon from the named parameter to get the key
                    $key = $extractPattern($namedParam);
                    // Check if the key exists in the provided parameters
                    if (array_key_exists($key, $params)) {
                        // Add the value to the positional parameters array
                        if (preg_match("/\[\s*:\s*(\w+)\s*\]/", $namedParam) && is_array($params[$key])) {
                            foreach ($params[$key] as $value) {
                                $positionalParams[] = $value;
                            }
                            $query = str_replace($namedParam, implode(',', array_fill(0, count((array)$params[$key]), '?')), $query);
                        } else {
                            // Add the value to the positional parameters array
                            $positionalParams[] = $params[$key];
                            // Replace the named parameter in the query with a ?
                            $query = str_replace($namedParam, '?', $query);
                        }
                    }
                }
                // Return the modified query and positional parameters
                return [$query, $positionalParams];
            };
            // Convert named parameters to positional placeholders
            list($query, $positionalParams) = $convertNamedToPositionalParams($query, $bindings);
            // Execute the statement
            return DB::{$statementType}($query, $positionalParams);
        });
    }
}
