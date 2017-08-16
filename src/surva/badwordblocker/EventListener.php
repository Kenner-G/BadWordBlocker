<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 06.10.16
 * Time: 18:05
 */

namespace surva\badwordblocker;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

class EventListener implements Listener {
    /* @var BadWordBlocker */
    private $badWordBlocker;

    public function __construct(BadWordBlocker $badWordBlocker) {
        $this->badWordBlocker = $badWordBlocker;
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onPlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $command = ("warn " . $player . " Using-Profanity 2");
        if($this->getBadWordBlocker()->contains($message, $this->getBadWordBlocker()->getList())) {
            $player->sendMessage($this->getBadWordBlocker()->getConfig()->get("blockmessage"));
            $server->dispatchCommand(new ConsoleCommandSender(), '$command');
            $event->setCancelled(true);

            return;
        }

        if(isset($player->lastwritten)) {
            if($player->lastwritten == $message) {
                $player->sendMessage($this->getBadWordBlocker()->getConfig()->get("lastwritten"));
                $event->setCancelled(true);

                return;
            }
        }

        if(isset($player->timewritten)) {
            if($player->timewritten > new \DateTime()) {
                $player->sendMessage($this->getBadWordBlocker()->getConfig()->get("timewritten"));
                $event->setCancelled(true);

                return;
            }
        }

        if(ctype_upper($message)) {
            $player->sendMessage($this->getBadWordBlocker()->getConfig()->get("caps"));
            $event->setCancelled(true);

            return;
        }

        $player->timewritten = new \DateTime();
        $player->timewritten = $player->timewritten->add(new \DateInterval("PT" . $this->getBadWordBlocker()->getConfig()->get("waitingtime") . "S"));
        $player->lastwritten = $message;

        $recipients = $event->getRecipients();
        $newrecipients = array();

        foreach($recipients as $recipient) {
            if(!isset($recipient->nochat)) {
                $newrecipients[] = $recipient;
            }
        }

        $event->setRecipients($newrecipients);
    }

    /**
     * @return BadWordBlocker
     */
    public function getBadWordBlocker(): BadWordBlocker {
        return $this->badWordBlocker;
    }
}
