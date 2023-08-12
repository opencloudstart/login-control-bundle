<?php

namespace LoginControl\src\Services;

use Exception;

class PasswordChecker
{
    /**
     * Flag for fopen() to indicate read only access
     */
    private const READ_ONLY_ACCESS = 'rb';

    /**
     * How many microseconds there are in a second
     */
    private const ONE_SECOND_IN_MICROSECONDS = 1000000;

    /**
     * How long we are happy to wait for the loop
     */
    private const LOOP_LIMIT_MICROSECONDS = self::ONE_SECOND_IN_MICROSECONDS / 4;

    /**
     * If a password is longer than this then we'll say it is long enough to look like it could be a passphrase
     */
    private const PASSPHRASE_MINIMUM_LENGTH = 15;

    /**
     * The space character
     */
    private const SPACE_CHAR = ' ';

    /**
     * @throws Exception
     */
    public function hackerCheckPassword(string $passwd): array
    {
        return [
            'HackerPhase' => self::looksLikeAPassPhrase($passwd),
            'DistanceToBadPassword' => self::findMinimumDistanceToBadPassword($passwd),
        ];
    }

    private static function looksLikeAPassPhrase(string $password): bool
    {
        $longEnough = mb_strlen($password) >= self::PASSPHRASE_MINIMUM_LENGTH;

        $containsSpaceChar = mb_strpos($password, self::SPACE_CHAR) !== 0;

        $containsOnlyPrintables = ctype_print($password);

        return $longEnough && $containsSpaceChar && $containsOnlyPrintables;
    }

    /**
     * @throws Exception
     */
    private static function findMinimumDistanceToBadPassword(string $password): int
    {
        try {

            $minimumLength = strlen($password);
            $fileHandle = self::getFileHandle();

            $startTime = microtime(true);

            while (!feof($fileHandle) && self::isRunLengthShorterThanLoopLimit($startTime)) {

                $badPassword = trim(fgets($fileHandle));

                $distance = levenshtein($password, $badPassword);

                if ($distance < $minimumLength) {
                    $minimumLength = $distance;
                }

                if ($distance === 0) {
                    break;
                }
            }

            return $minimumLength;

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } finally {

            if (isset($fileHandle)) {
                fclose($fileHandle);
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function getFileHandle(string $filename = "password_list.txt")
    {
        $dataPath = __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
        $fileHandle = fopen($dataPath . $filename, self::READ_ONLY_ACCESS);

        if (false === $fileHandle) {
            throw new Exception("Could not open password file for reading.");
        }

        return $fileHandle;
    }

    public static function isRunLengthShorterThanLoopLimit($startTimeMicroseconds): bool
    {
        $runLength = microtime(true) - $startTimeMicroseconds;
        return $runLength < self::LOOP_LIMIT_MICROSECONDS;
    }

}
