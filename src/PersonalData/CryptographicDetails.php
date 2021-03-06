<?php namespace EventSourcery\EventSourcery\PersonalData;

use EventSourcery\EventSourcery\Serialization\SerializableValue;

/**
 * CryptographicDetails is the value object that contains the
 * necessary details to encrypt and decrypt personal data for
 * a single person.
 */
class CryptographicDetails implements SerializableValue {

    /**
     * a key => value array of details
     * @var array
     */
    private $details;
    /**
     * the name of the encryption type used
     * @var string
     */
    private $encryption;

    public function __construct(string $encryption, array $details) {
        $this->encryption = $encryption;
        $this->details    = $details;
    }

    /**
     * get the type of encryption that these details were created for
     *
     * @return string
     */
    public function encryption(): string {
        return $this->encryption;
    }

    /**
     * get the value for a key from the cryptographic details
     *
     * @param $name
     * @return string
     * @throws CryptographicDetailsDoNotContainKey
     */
    public function key($name): string {
        if ( ! isset($this->details[$name])) {
            throw new CryptographicDetailsDoNotContainKey($name);
        }
        return $this->details[$name];
    }

    /**
     * serialize() returns an associated array form of the
     * value for persistence which will be deserialized when needed.
     *
     * the array should contain primitives for both keys and values.
     *
     * @return array
     */
    public function serialize(): array {
        // base 64 encode
        $details = array_map(function($value) {
            return base64_encode($value);
        }, $this->details);

        // enrich with encryption type
        return $details + ['encryption' => $this->encryption];
    }

    /**
     * deserialize() returns a value object from an associative array received
     * from persistence
     *
     * @param array $data
     * @return mixed
     * @throws CannotDeserializeCryptographicDetails
     */
    public static function deserialize(array $data): CryptographicDetails {
        // json string to array
        if ( ! isset($data['encryption'])) {
            throw new CannotDeserializeCryptographicDetails('Encryption type could not be identified from serialized form.');
        }

        // pull out the encryption key
        $encryption = $data['encryption'];
        unset($data['encryption']);

        // base64 decode
        $details = array_map(function($value) {
            return base64_decode($value);
        }, $data);

        return new static($encryption, $details);
    }
}