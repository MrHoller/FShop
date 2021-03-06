<?php

namespace mrholler\fshop\libs\xenialdan\customui\elements;

use InvalidArgumentException;
use pocketmine\player\Player;

class Slider extends UIElement
{

    /** @var float */
    protected $defaultValue = 0.0;

    /**
     * @param string $text
     * @param float $min
     * @param float $max
     * @param float $step
     * @throws InvalidArgumentException
     */
    public function __construct(protected string $text = "", protected float $min = 0.0, protected float $max = 0.0, protected float $step = 0.0)
    {
        if ($this->min > $this->max) {
            throw new InvalidArgumentException(__METHOD__ . ' Borders are messed up');
        }
        $this->defaultValue = $this->min;
        $this->setStep($this->step);
    }

    /**
     *
     * @param float $step
     * @throws InvalidArgumentException
     */
    public function setStep(float $step): void
    {
        if ($step < 0) {
            throw new InvalidArgumentException(__METHOD__ . ' Step should be positive');
        }
        $this->step = $step;
    }

    /**
     * @param float $value
     * @throws InvalidArgumentException
     */
    public function setDefaultValue(float $value): void
    {
        if ($value < $this->min || $value > $this->max) {
            throw new InvalidArgumentException(__METHOD__ . ' Default value out of borders');
        }
        $this->defaultValue = $value;
    }

    final public function jsonSerialize(): array
    {
        $data = [
            'type' => 'slider',
            'text' => $this->text,
            'min' => $this->min,
            'max' => $this->max
        ];
        if ($this->step > 0) {
            $data['step'] = $this->step;
        }
        if ($this->defaultValue !== $this->min) {
            $data['default'] = $this->defaultValue;
        }
        return $data;
    }

    /**
     * Returns the float value it was set to
     *
     * @param string $value
     * @param Player $player
     * @return float
     */
    public function handle(string $value, Player $player)
    {
        return $value;
    }

}
