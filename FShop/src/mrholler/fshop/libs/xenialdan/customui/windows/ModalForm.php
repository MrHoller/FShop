<?php

namespace mrholler\fshop\libs\xenialdan\customui\windows;

use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use RuntimeException;
use mrholler\fshop\libs\xenialdan\customui\elements\UIElement;

class ModalForm implements CustomUI
{
    use CallableTrait;

    /** @var int */
    private $id;

    /**
     * This is a window to show a simple text to the player
     *
     * @param string $title
     * @param string $content
     * @param string $trueButtonText
     * @param string $falseButtonText
     */
    public function __construct(protected string $title = "", protected string $content = "", protected string $trueButtonText = "", protected string $falseButtonText = ""){}

    final public function jsonSerialize()
    {
        return [
            'type' => 'modal',
            'title' => $this->title,
            'content' => $this->content,
            'button1' => $this->trueButtonText,
            'button2' => $this->falseButtonText,
        ];
    }

    final public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): array
    {
        return [$this->content, $this->trueButtonText, $this->falseButtonText];
    }

    public function setID(int $id): void
    {
        $this->id = $id;
    }

    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $index
     * @return UIElement|null
     */
    public function getElement(int $index): ?UIElement
    {
        return null;
    }

    public function setElement(UIElement $element, int $index): void
    {
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     * @param null $callableClose
     * @throws RuntimeException
     */
    public function setCallableClose($callableClose = null): void
    {
        Server::getInstance()->getLogger()->debug('[' . __METHOD__ . '] ' . TextFormat::RED . 'Due to a client bug modal forms send false when closed, so this function will never be called!');
    }

    /**
     * Handles a form response from a player.
     *
     * @param Player $player
     * @param mixed $data
     *
     * @throws FormValidationException if the data could not be processed
     */
    public function handleResponse(Player $player, $data): void
    {
        if (!is_bool($data)) {
            throw new FormValidationException('Expected bool, got ' . gettype($data));
        }
        $callable = $this->getCallable();
        if ($callable !== null) {
            $callable($player, (bool)$data);
        }
    }
}
