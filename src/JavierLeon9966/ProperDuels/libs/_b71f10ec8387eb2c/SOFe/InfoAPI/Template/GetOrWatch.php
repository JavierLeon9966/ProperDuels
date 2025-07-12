<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\Template;

use Closure;
use pocketmine\command\CommandSender;
use RuntimeException;
use Shared\SOFe\InfoAPI\Mapping;
use Shared\SOFe\InfoAPI\Parameter;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\AwaitGenerator\Traverser;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\Ast;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\Ast\MappingCall;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\Pathfind;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\ReadIndices;

use function array_keys;
use function array_map;
use function count;
use function implode;
use function json_decode;
use function range;
use function sprintf;




























































































































































































































/**
 * @template R of RenderedElement
 * @template G of RenderedGroup
 */
interface GetOrWatch {
	/**
	 * @param R[] $elements
	 * @return G
	 */
	public function buildResult(array $elements) : RenderedGroup;

	/**
	 * @return EvalChain<R>
	 */
	public function startEvalChain() : EvalChain;

	/**
	 * @return R
	 */
	public function staticElement(string $raw) : RenderedElement;
}