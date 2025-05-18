import { CommandDialog, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { CommandIcon, SearchIcon } from 'lucide-react';

export default function AppCommand() {
  const [open, setOpen] = useState(false);

  useEffect(() => {
    const down = (e: KeyboardEvent) => {
      if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
        e.preventDefault();
        setOpen((open) => !open);
      }
    };

    document.addEventListener('keydown', down);
    return () => document.removeEventListener('keydown', down);
  }, []);

  return (
    <div>
      <Button className="px-1!" variant="outline" size="sm" onClick={() => setOpen(true)}>
        <span className="sr-only">Open command menu</span>
        <SearchIcon className="ml-1 size-3" />
        Search...
        <span className="bg-accent flex h-6 items-center justify-center rounded-sm border px-2 text-xs">
          <CommandIcon className="mr-1 size-3" /> K
        </span>
      </Button>
      <CommandDialog open={open} onOpenChange={setOpen}>
        <CommandInput placeholder="Type a command or search..." />
        <CommandList>
          <CommandEmpty>No results found.</CommandEmpty>
          <CommandGroup heading="Suggestions">
            <CommandItem>Create server</CommandItem>
            <CommandItem>Create project</CommandItem>
          </CommandGroup>
        </CommandList>
      </CommandDialog>
    </div>
  );
}
