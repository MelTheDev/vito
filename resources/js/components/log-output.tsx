import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { ReactNode, useRef, useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { ArrowDown, ClockArrowDownIcon } from 'lucide-react';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';

export default function LogOutput({ children }: { children: ReactNode }) {
  const scrollRef = useRef<HTMLDivElement>(null);
  const endRef = useRef<HTMLDivElement>(null);
  const [autoScroll, setAutoScroll] = useState(false);

  useEffect(() => {
    if (autoScroll && endRef.current) {
      endRef.current.scrollIntoView({ behavior: 'smooth' });
    }
  }, [children, autoScroll]);

  const toggleAutoScroll = () => {
    setAutoScroll(!autoScroll);
  };

  return (
    <div className="relative">
      <ScrollArea ref={scrollRef} className="bg-accent/50 text-accent-foreground relative h-[500px] w-full p-4 font-mono text-sm whitespace-pre-line">
        {children}
        <div ref={endRef} />
        <ScrollBar orientation="vertical" />
      </ScrollArea>
      <Button
        variant="outline"
        size="icon"
        className="bg-accent! absolute right-4 bottom-4"
        onClick={toggleAutoScroll}
        title={autoScroll ? 'Disable auto-scroll' : 'Enable auto-scroll'}
      >
        <Tooltip>
          <TooltipTrigger asChild>
            <div>{autoScroll ? <ClockArrowDownIcon className="h-4 w-4" /> : <ArrowDown className="h-4 w-4" />}</div>
          </TooltipTrigger>
          <TooltipContent>{autoScroll ? 'Turn off auto scroll' : 'Auto scroll down'}</TooltipContent>
        </Tooltip>
      </Button>
    </div>
  );
}
